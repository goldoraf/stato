<?php

class Stato_ActionNotFound extends Exception {}
class Stato_DoubleRespond extends Exception {}
class Stato_MissingTemplate extends Exception {}

/**
 * Controller class
 * 
 * Controllers are made up of public methods or "actions" that are executed on request
 * and then either render a template or redirect to another action.
 * 
 * @package Stato
 * @subpackage webflow
 */
class Stato_Controller
{
    /**
     * Holds the request object that's primarily used to get GET, POST and other 
     * environment variables
     * @var Stato_Request
     */
    protected $request;
    
    /**
     * Holds the response object that's primarily used to set additional HTTP headers
     * and that holds the final body content that's sent back to the browser
     * @var Stato_Response
     */
    protected $response;
    
    /**
     * Holds the session object that's used to persist state between requests
     * @var Stato_Session
     */
    protected $session;
    
    /**
     * Holds the chain of filters that are run before action code processing
     * @var Stato_FilterChain
     */
    protected $beforeFilters;
    
    /**
     * Holds the chain of filters that are run after action code processing
     * @var Stato_FilterChain
     */
    protected $afterFilters;
    
    /**
     * Holds the chain of filters that are run before and after action code processing
     * @var Stato_FilterChain
     */
    protected $aroundFilters;
    
    /**
     * Default template rendering options
     * @var array
     */
    protected $defaultRenderOptions = array(
        'status' => 200, 'layout' => false, 'locals' => array()
    );
    
    /**
     * Holds the HTTP status code to send back
     * @var integer
     */
    private $status = 200;
    
    /**
     * Directories that contain templates
     * @var array
     */
    private $viewDirs = array();
    
    /**
     * Whether or not the respond method has been called
     * @var boolean
     */
    private $performedRespond = false;
    
    /**
     * Constructor
     * 
     * @param Stato_Request $request
     * @param Stato_Response $response
     * @return void
     */
    public function __construct(Stato_Request $request, Stato_Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->session = new Stato_Session();
        $this->beforeFilters = new Stato_FilterChain();
        $this->afterFilters  = new Stato_FilterChain();
        $this->aroundFilters = new Stato_FilterChain();
    }
    
    /**
     * Calls the action specified in the request object and returns a response
     * 
     * @return Stato_Response
     */
    public function run()
    {
        $this->initialize();
        
        $action = $this->getActionName();
        if (!$this->actionExists($action))
            throw new Stato_ActionNotFound($action);
            
        $this->session->start();
            
        $beforeResult = $this->beforeFilters->process($this, $action, 'before');
        $this->aroundFilters->process($this, $action, 'before');
        
        if ($beforeResult !== false && !$this->performedRespond) {
            $content = $this->$action();
            if (!$this->performedRespond) $this->respond($content);
        }
        
        $this->aroundFilters->process($this, $action, 'after');
        $this->afterFilters->process($this, $action, 'after');
        
        $this->session->store();
        
        return $this->response;
    }
    
    /**
     * Specifies a directory as containing templates
     * 
     * @param string $path
     * @return void
     */
    public function addViewDir($dir)
    {
        $this->viewDirs[] = $dir;
    }
    
    /**
     * Sets the session persistence handler
     * 
     * @param Stato_ISessionHandler $handler
     * @return void
     */
    public function setSessionHandler(Stato_ISessionHandler $handler)
    {
        $this->session->setHandler($handler);
    }
    
    /**
     * Runs a filter
     * 
     * @param string $method
     * @return boolean
     */
    public function callFilter($method)
    {
        return $this->$method();
    }
    
    /**
     * Overwrite to perform initializations prior to action call
     */
    protected function initialize()
    {
        
    }
    
    /**
     * Renders the specified action template
     * 
     * @param mixed $action
     * @param array $options
     * @return string
     */
    protected function render($action = null, $options = array())
    {
        if (is_array($action)) {
            $options = array_merge($action, $options);
            $action = null;
        }
        $options = array_merge($this->defaultRenderOptions, $options);
        
        if (isset($options['status']))
            $this->status = $options['status'];
        
        if (isset($options['template']))
            $template = $options['template'];
        else
            $template = $this->getTemplateName($action);
        
        if ($options['layout'] !== false)
            return $this->renderWithLayout($template, $options['layout'], $options['locals']);
        
        return $this->renderTemplate($template, $options['locals']);
    }
    
    /**
     * Prepares for redirection to an url
     *
     * Sets Location header and response code.
     *
     * @param string $url
     * @param boolean $permanently
     * @return void
     */
    protected function redirect($url, $permanently = false)
    {
        $this->response->setHeader('Location', $url);
        $this->respond(
            "<html><body>You are being <a href=\"{$url}\">redirected</a>.</body></html>",
            ($permanently) ? 301 : 302
        );
    }
    
    /**
     * Sets a HTTP 1.1 Cache-Control header of "no-cache" so no caching 
     * should occur by the browser or intermediate caches
     *
     * @return void
     */
    protected function expiresNow()
    {
        $this->response->setHeader('Cache-Control', 'no-cache');
    }
    
    /**
     * Sets the response body and status code header
     *
     * @param mixed $data
     * @param integer $status
     * @return void
     */
    protected function respond($data, $status = null)
    {
        if ($this->performedRespond)
            throw new Stato_DoubleRespond();
        
        $this->response->setStatus(!empty($status) ? $status : $this->status);
        $this->response->setBody($data);
        $this->performedRespond = true;
    }
    
    /**
     * Renders a partial template
     * 
     * @param mixed $template
     * @param array $options
     * @return string
     */
    protected function partial($template, $options = array())
    {
        if (isset($options['collection']))
            return $this->partialCollection($template, $options);
            
        $locals = (isset($options['locals'])) ? $options['locals'] : array();
        return $this->renderTemplate($template, $locals);
    }
    
    /**
     * Sends a file over HTTP
     * 
     * @param string $path
     * @param array $params
     * @return void
     */
    protected function sendFile($path, $params = array())
    {
        if (!file_exists($path) || !is_readable($path)) 
            throw new Exception('Cannot read file : '.$path);
        
        $defaults = array(
            'type' => 'application/octet-stream',
            'disposition' => 'attachment',
            'stream' => true
        );
        $params = array_merge($defaults, $params);
        
        if (!isset($params['filename'])) $params['filename'] = basename($path);
        if (!isset($params['length']))   $params['length']   = filesize($path);
        
        $this->setFileHeaders($params);
        
        /*if ($params['stream'] === true)
        {
            $this->response->send_headers();
            $fp = @fopen($path, "rb");
            fpassthru($fp);
            return;
        }
        else */$this->respond(file_get_contents($path));
    }
    
    /**
     * Sends binary data over HTTP to the user as a file download
     * 
     * @param string $data
     * @param array $params
     * @return void
     */
    protected function sendData($data, $params = array())
    {
        $defaults = array(
            'type' => 'application/octet-stream',
            'disposition' => 'attachment',
        );
        $params = array_merge($defaults, $params);
        
        if (!isset($params['length']) && !is_resource($data)) $params['length'] = strlen($data);
        
        $this->setFileHeaders($params);
        
        /*if (is_resource($data))
        {
            $this->response->send_headers();
            rewind($data);
            fpassthru($data);
            exit();
        }*/
        $this->respond($data);
    }
    
    /**
     * Converts the controller class name from something like "WeblogController" to "weblog"
     * 
     * @return string
     */
    protected function getControllerName()
    {
        return underscore(str_replace('Controller', '', get_class($this)));
    }
    
    /**
     * Returns the requested action name (default to "index")
     * 
     * @return string
     */
    protected function getActionName()
    {
        $action = $this->request->getParam('action');
        if (empty($action)) $action = 'index';
        return $action;
    }
    
    /**
     * Returns the template name related to the requested action
     * 
     * E.g: FooController::new() => foo/new
     * 
     * @param string $actionName
     * @return string
     */
    protected function getTemplateName($actionName = null)
    {
        if (strpos($actionName, '/') !== false) return $actionName;
        if ($actionName === null) $actionName = $this->getActionName();
        return $this->getControllerName().'/'.$actionName;
    }
    
    /**
     * Sets the default layout to use
     * 
     * You can disable layout rendering by passing false to this method.
     * 
     * @param string $layout
     * @return void
     */
    protected function setLayout($layout)
    {
        $this->defaultRenderOptions['layout'] = ($layout) ? $layout : false;
    }
    
    /**
     * Checks if a public method named after the requested action exists
     * 
     * @param string $action
     * @return boolean
     */
    protected function actionExists($action)
    {
        try {
            $method = new ReflectionMethod(get_class($this), $action);
            return ($method->isPublic() && !$method->isConstructor()
                    && $method->getDeclaringClass()->getName() != __CLASS__);
        }
        catch (ReflectionException $e) {
             return false;
        }
    }
    
    /**
     * Renders a template
     * 
     * @param string $template
     * @param array $locals
     * @return string
     */
    private function renderTemplate($template, $locals = array())
    {
        $templatePath = $this->getTemplatePath($template);
        
        if (!is_readable($templatePath))
            throw new Stato_MissingTemplate($templatePath);
        
        extract($locals);
        ob_start();
        include ($templatePath);
        return ob_get_clean();
    }
    
    /**
     * Renders a template inside a layout
     * 
     * @param string $template
     * @param string $layout
     * @param array $locals
     * @return string
     */
    private function renderWithLayout($template, $layout, $locals = array())
    {
        $layout = "layouts/{$layout}";
        $this->contentForLayout = $this->renderTemplate($template, $locals);
        return $this->renderTemplate($layout, $locals);
    }
    
    /**
     * Renders a collection of partials
     * 
     * @param string $template
     * @param array $options
     * @return string
     */
    private function partialCollection($template, $options)
    {
        $partials = array();
        $elementName = basename($template, '.php');
        $counterName = "{$elementName}_counter";
        $counter = 1;
        foreach($options['collection'] as $element)
        {
            $locals[$counterName] = $counter;
            $locals[$elementName] = $element;
            $partials[] = $this->renderTemplate($this->getTemplateName($template), $locals);
            $counter++;
        }
        
        if (isset($options['spacer'])) 
            $spacer = $options['spacer'];
        elseif (isset($options['spacer_template'])) 
            $spacer = $this->renderTemplate($this->getTemplateName($options['spacer_template']));
        else 
            $spacer = '';

        return implode($spacer, $partials);
    }
    
    /**
     * Returns the absolute path of a template (if found)
     * 
     * @param string $template
     * @return string
     */
    private function getTemplatePath($template)
    {
        if (file_exists($template)) return $template;
        foreach ($this->viewDirs as $dir) {
            $possiblePath = "{$dir}/{$template}.php";
            if (file_exists($possiblePath)) return $possiblePath;
        }
        throw new Stato_MissingTemplate($template);
    }
    
    /**
     * Sets response headers for a file sending
     * 
     * @param array $params
     * @return void
     */
    private function setFileHeaders($params = array())
    {
        $disposition = $params['disposition'];
        if (isset($params['filename'])) 
            $disposition.= '; filename='.$params['filename'];
        
        $headers = array(
            'Content-Length'      => $params['length'],
            'Content-Type'        => $params['type'],
            'Content-Disposition' => $disposition,
            'Content-Transfer-Encoding' => 'binary'
        );
        foreach ($headers as $k => $v) $this->response->setHeader($k, $v);
    }
}
