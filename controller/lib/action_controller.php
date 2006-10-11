<?php

class SUnknownControllerException extends SException {}
class SUnknownActionException extends SException {}
class SUnknownProtocolException extends SException {}
class SDoubleRenderException extends SException {}

/**
 * Front-web controller class
 * 
 * Action Controllers are made up of one or more actions that performs its purpose
 * and then either renders a template or redirects to another action. An action 
 * is defined as a public method on the controller, which will automatically be 
 * made accessible to the web-server through Routing. 
 * A sample controller could look like this:
 * 
 * <code>class WeblogController extends SActionController
 *{
 *     public function index()
 *     {
 *         $this->posts = SActiveStore::findAll('Post');
 *     }
 *      
 *     public function add_comment()
 *     {
 *         ...
 *     }   
 *}</code>
 * 
 * @package Stato
 * @subpackage controller
 */
class SActionController
{   
    /**
     * Holds the request object that's primarily used to get variables set by the 
     * web server or otherwise directly related to the execution environment.
     * Examples : <var>$this->request->isPost(); $this->request->requestUri();</var>     
     */
    public $request  = null;
    /**
     * Holds the session object that can be used to register variables to the session.
     * Accessed like <var>$this->session['user']</var>.          
     */
    public $session  = null;
    public $response = null;
    public $assigns  = null;
    public $params   = null;
    public $view     = null;
    public $flash    = null;
    public $logger   = null;
    
    protected $layout   = false;
    protected $models   = array();
    protected $helpers  = array();
    protected $scaffold = null;
    
    protected $hiddenActions  = array();
    
    protected $cachedPages    = array();
    protected $cachedActions  = array();
    protected $pageCacheDir   = null;
    protected $pageCacheExt   = '.html';
    protected $performCaching = True;
    
    protected $beforeFilters = array();
    protected $afterFilters  = array();
    protected $aroundFilters = array();
    
    protected $skipBeforeFilters = array();
    protected $skipAfterFilters  = array();
    
    private $subDirectory      = null;
    
    private $performedRender   = false;
    private $performedRedirect = false;
    
    public $parentController  = null;
    
    const DEFAULT_RENDER_STATUS_CODE = '200 OK';
    
    public static function factory($request, $response)
    {
        if ($request->controller == 'api') 
            return self::dispatchWebServiceRequest($request, $response);
		else 
            return self::instanciateController($request->controller)->process($request, $response);
    }
    
    public static function processWithException($request, $response, $exception)
    {
        $controller = new SActionController();
        return $controller->process($request, $response, 'rescueAction', $exception);
    }
    
    public static function processWithComponent($class, $request, $response, $parentController = null)
    {
        $controller = new $class();
        $controller->parentController = $parentController;
        return $controller->process($request, $response);
    }
    
    public function process($request, $response, $method = 'performAction', $arguments = null)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->params   =& $this->request->params;
        $this->assigns  =& $this->response->assigns;
        
        if ($arguments != null) $this->$method($arguments);
        else $this->$method();
        
        return $this->response;
    }
    
    public function invokeDirectWebService($request)
    {
        return call_user_func_array(array(&$this, $request->method), $request->params);
    }
    
    public function __construct()
    {
        $this->view    = new SActionView($this);
        $this->logger  = SLogger::getInstance();
        
        $this->pageCacheDir = ROOT_DIR.'/public/cache';
    }
    
    public function __get($name)
    {
        if (isset($this->assigns[$name])) return $this->assigns[$name];
    }
    
    public function __set($name, $value)
    {
        $this->assigns[$name] = $value;
    }
    
    /**
     * Converts the class name from something like "WeblogController" to "weblog"
     */
    public function controllerName()
    {
        return SInflection::underscore(str_replace('Controller', '', $this->controllerClassName()));
    }
    
    /**
     * Returns the class name
     */
    public function controllerClassName()
    {
        return get_class($this);
    }
    
    public function controllerPath()
    {
        return SDependencies::subDirectory(get_class($this)).$this->controllerName();
    }
    
    public function actionName()
    {
        if (empty($this->request->action)) return 'index';
        return $this->request->action;
    }
    
    public function urlFor($options = array())
    {
        if (!isset($options['controller']))
        {
            $options['controller'] = $this->controllerPath();
            if (!isset($options['action'])) $options['action'] = $this->actionName();
        }
        elseif (!isset($options['action'])) $options['action'] = 'index';  
        
        return SUrlRewriter::rewrite($options);
    }
    
    /**
     * Overwrite to perform initializations prior to action call
     */
    protected function initialize()
    {
    
    }
    
    protected function render($status = null)
    {
        $this->renderAction($this->actionName(), $status);
    }
    
    protected function renderAction($action, $status = null)
    {
        $template = $this->templatePath($this->controllerPath(), $action);
        if (!file_exists($template)) throw new SException('Template not found for this action');
        
        if ($this->layout) $this->renderWithLayout($template, $status);
        else $this->renderFile($template, $status);
    }
    
    protected function renderWithLayout($template, $status = null)
    {
        $this->addVariablesToAssigns();
        $this->assigns['layout_content'] = $this->view->render($template);
        
        $layout = APP_DIR.'/views/layouts/'.$this->layout.'.php';
        if (!file_exists($layout)) throw new SException('Layout not found');
        $this->renderFile($layout, $status);
    }
    
    protected function renderFile($path, $status = null)
    {
        $this->addVariablesToAssigns();
        $this->renderText($this->view->render($path), $status);
    }
    
    protected function renderComponent($options = array())
    {
        $this->renderText($this->componentResponse($options, true)->body); 
    }
    
    protected function renderPartial($partial, $localAssigns = array())
    {
        $this->addVariablesToAssigns();
        if (strpos($partial, '/') === false) $partial = $this->controllerPath().'/'.$partial;
        $this->renderText($this->view->renderPartial($partial, $localAssigns));
    }
    
    /**
     * Renders text without the active layout. 
     * 
     * It is usually used for rendering prepared content by renderFile() and 
     * Action Caching, but you can use it for tests.    
     * Note that it must remain public instead of protected because SActionCacheFilter
     * must be able to call it directly.
     */
    public function renderText($str, $status = null)
    {
        if ($this->isPerformed())
            throw new SDoubleRenderException('Can only render or redirect once per action');
        
        $this->performedRender = true;
        $this->response->headers['Status'] = (!empty($status)) ? $status : self::DEFAULT_RENDER_STATUS_CODE;
        $this->response->headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->response->body = $str;
    }
    
    protected function templatePath($controllerPath, $action)
    {
        return APP_DIR."/views/$controllerPath/$action.php";
    }
    
    protected function addVariablesToAssigns()
    {
        $this->assigns['params'] = $this->params;
        if (isset($this->flash) && isset($this->session))
        {
            if (!$this->flash->isEmpty()) $this->assigns['flash'] = $this->flash->dump();
            $this->flash->discard();
            $this->assigns['session'] = $this->session;
        }
    }
    
    protected function redirectTo($options)
    {
        if (is_array($options))
        {
            $this->redirectTo($this->urlFor($options));
            //$this->response->redirectedTo = $options;
        }
        elseif (preg_match('#^\w+://.*#', $options))
        {
            if ($this->isPerformed())
                throw new SDoubleRenderException('Can only render or redirect once per action');
            
            $this->logger->info("Redirected to {$options}");
            $this->response->redirect($options);
            $this->response->redirectedTo = $options;
            $this->performedRedirect = true;
        }
        else
        {
            $this->redirectTo($this->request->protocol().$this->request->hostWithPort().$options);
        }
    }
    
    protected function redirectBack()
    {
        if (isset($_SERVER['HTTP_REFERER'])) $this->redirectTo($_SERVER['HTTP_REFERER']);
        else
        {
            throw new SException('No HTTP_REFERER was set in the request to this action, 
                so redirectBack() could not be called successfully');
        }
    }
    
    protected function expiresIn($seconds, $options = array())
    {
        $cacheOptions = array_merge(array('max-age' => $seconds, 'private' => true), $options);
        $cacheControl = array();
        foreach ($cacheOptions as $k => $v)
        {
            if ($v === false || $v === null) unset($cacheOptions[$k]);
            if ($v === true) $cacheControl[] = $k;
            else $cacheControl[] = "$k=$v";
        }
        $this->response->headers['Cache-Control'] = implode(',', $cacheControl);
    }
    
    protected function expiresNow()
    {
        $this->response->headers['Cache-Control'] = 'no-cache';
    }
    
    protected function eraseResults()
    {
        $this->eraseRenderResults();
        $this->eraseRedirectResults();
    }
    
    protected function eraseRenderResults()
    {
        $this->response->body = '';
        $this->performedRender = false;
    }
    
    protected function eraseRedirectResults()
    {
        $this->performedRedirect = false;
        $this->response->redirectedTo = null;
        $this->response->headers['Status'] = self::DEFAULT_RENDER_STATUS_CODE;
        unset($this->response->headers['location']);
    }
    
    protected function sendFile($path, $params=array())
    {
        if (!file_exists($path) || !is_readable($path)) 
            throw new SException('Cannot read file : '.$path);
        
        $defaults = array
        (
            'type' => 'application/octet-stream',
            'disposition' => 'attachment',
            'stream' => true
        );
        $params = array_merge($defaults, $params);
        
        if (!isset($params['filename'])) $params['filename'] = basename($path);
        if (!isset($params['length']))   $params['length']   = filesize($path);
        
        $this->sendFileHeaders($params);
        
        if ($params['stream'] === true)
        {
            $this->response->sendHeaders();
            $fp = @fopen($path, "rb");
            fpassthru($fp);
            exit();
        }
        else $this->renderText(file_get_contents($path));
    }
    
    protected function cachePage($content = null, $options = array())
    {
        if (!$this->performCaching) return;
        
        if ($content == null) $content = $this->response->body;
        if (is_array($options))
            $path = $this->urlFor(array_merge($options, array('only_path' => true, 'skip_relative_url_root' => true)));
        else 
            $path = $options;
        
        if (!SFileUtils::mkdirs(dirname($this->pageCachePath($path)), 0700, true))
            throw new SException('Caching failed with dirs creation');
        file_put_contents($this->pageCachePath($path), $content);
    }
    
    protected function expirePage($options = array())
    {
        if (!$this->performCaching) return;
        
        if (is_array($options))
            $path = $this->urlFor(array_merge($options, array('only_path' => true, 'skip_relative_url_root' => true)));
        else 
            $path = $options;
            
        if (file_exists($this->pageCachePath($path))) unlink($this->pageCachePath($path));
    }
    
    protected function expireFragment($id)
    {
        if (!$this->performCaching) return;
        
        if (is_array($id))
            list($protocol, $id) = explode('://', $this->urlFor($id));
        
        $file = ROOT_DIR."/cache/fragments/{$id}";
        if (file_exists($file)) unlink($file);
    }
    
    protected function paginate($className, $perPage=10, $options=array())
    {
        if (isset($options['parameter']))  $param = $options['parameter'];
        else $param = 'page';
        
        if (isset($this->request->params[$param]))
            $currentPage = $this->request->params[$param];
        else
            $currentPage = 1;
        
        $paginator = new SPaginator($className, $perPage, $currentPage, $options);
        return array($paginator, $paginator->currentPage());
    }
    
    private function performAction()
    {
        $action = $this->actionName();
        if (!$this->actionExists($action) && $this->scaffold === null)
            throw new SUnknownActionException("Action $action not found in ".$this->controllerClassName());
            
        if ($this->scaffold !== null)
        {
            $this->renderComponent(array('controller' => 'scaffolding', 'action' => $action,
                                         'scaffold' => $this->scaffold));
            return;
        }
        
        $this->initialize();
        $this->requireDependencies();
        
        $this->session = new SSession();
        $this->flash   = new SFlash($this->session);
        
        if (!empty($this->cachedActions))
            $this->aroundFilters[] = new SActionCacheFilter($this->cachedActions);
        
        $beforeResult = $this->processFilters('before');
        foreach($this->aroundFilters as $filter) $filter->before($this);
        
        if ($beforeResult !== false && !$this->isPerformed())
        {
            $this->$action();
            if (!$this->isPerformed()) $this->render();
        }
        
        foreach($this->aroundFilters as $filter) $filter->after($this);
        $this->processFilters('after');
        
        if (in_array($this->actionName(), $this->cachedPages) && $this->performCaching && $this->isCachingAllowed())
            $this->cachePage($this->response->body, array('action' => $this->actionName(), 'params' => $this->params));
            
        $this->logProcessing();
        $this->logBenchmarking();
    }
    
    private function actionExists($action)
    {
        try
        {
            $method = new ReflectionMethod(get_class($this), $action);
            return ($method->isPublic() && !$method->isConstructor()
                    && $method->getDeclaringClass()->getName() != __CLASS__
                    && !in_array($action, $this->hiddenActions));
        }
        catch (ReflectionException $e)
        {
             return false;
        }
    }
    
    private function requireDependencies()
    {
        SLocale::loadStrings(APP_DIR.'/i18n/'.SDependencies::subDirectory(get_class($this)));
        SUrlRewriter::initialize($this->request);
        
        foreach($this->helpers as $k => $helper) $this->helpers[$k] = $helper.'Helper';
        
        SDependencies::requireDependencies('models', $this->models, get_class($this));
        SDependencies::requireDependencies('helpers', $this->helpers, get_class($this));
    }
    
    private function componentResponse($options, $reuseResponse)
    {
        $controller = $options['controller'];
        $class = SInflection::camelize($controller).'Controller';
        
        if (!file_exists($path = APP_DIR."/components/{$controller}/{$controller}_controller.php"))
    		throw new SUnknownControllerException(ucfirst($reqController).' Component not found !');
    		
    	require_once($path);
        
        $request = $this->requestForComponent($options);
        $response = ($reuseResponse) ? $this->response : new SResponse();
        return SActionController::processWithComponent($class, $request, $response, $this);
    }
    
    private function requestForComponent($options)
    {
        $request = clone $this->request;
        $request->params = array_merge($request->params, $options);
        return $request;
    }
    
    private function processFilters($state)
    {
        $prop = $state.'Filters';
        foreach ($this->$prop as $filter)
        {
            if (is_array($filter))
            {
                $method = $filter[0];
                
                if (isset($filter['only']) && !is_array($filter['only']))
                    $filter['only'] = array($filter['only']);
                if (isset($filter['except']) && !is_array($filter['except']))
                    $filter['except'] = array($filter['except']);
                
                if ((isset($filter['only']) && in_array($this->actionName(), $filter['only']))
                    || (isset($filter['except']) && !in_array($this->actionName(), $filter['except']))
                    || (!isset($filter['only']) && !isset($filter['except'])))
                    $result = $this->callFilter($method, $state);
            }
            else $result = $this->callFilter($filter, $state);
            
            if ($result === false) return false;
        }
    }
    
    private function callFilter($method, $state)
    {
        $skipProp = 'skip'.ucfirst($state).'Filters';
        if (!in_array($method, $this->$skipProp)) return $this->$method();
    }
    
    private function isPerformed()
    {
        return ($this->performedRender || $this->performedRedirect);
    }
    
    private function sendFileHeaders($params = array())
    {
        $disposition = $params['disposition'];
        if (isset($params['filename'])) $disposition.= '; filename='.$params['filename'];
        $headers = array
        (
            'Content-Length'      => $params['length'],
            'Content-Type'        => $params['type'],
            'Content-Disposition' => $disposition,
            'Content-Transfer-Encoding' => 'binary'
        );
        $this->response->headers = array_merge($this->response->headers, $headers);
        // IE6 fix on opening downloaded files
        /*if ($this->response->headers['Cache-Control'] == 'no-cache')
            $this->response->headers['Cache-Control'] = 'private';*/
    }
    
    private function logProcessing()
    {
        $log = "\n\nProcessing ".$this->controllerClassName().'::'.$this->actionName()
            .'() for '.$this->request->remoteIp().' at '
            .SDateTime::today()->__toString().' ['.$this->request->method().']';
        if (($sessId = $this->session->sessionId()) != '') $log.= "\n    Session ID: ".$sessId;
        $log.= "\n    Parameters: ".serialize($this->params);
        $this->logger->info($log);
    }
    
    private function logBenchmarking()
    {
        $runtime = microtime(true) - STATO_TIME_START;
        $dbRuntime = SActiveRecord::connection()->runtime;
        $dbPercentage = ($dbRuntime * 100) / $runtime;
        $this->logger->info('Completed in '.sprintf("%.5f", $runtime)
                            .' seconds | DB: '.sprintf("%.5f", $dbRuntime).' ('.sprintf("%d", $dbPercentage).' %)');
    }
    
    private function rescueAction($exception)
    {
        if ($this->isPerformed()) $this->eraseResults();
        $this->logError($exception);
        if (APP_MODE == 'dev') $this->rescueActionLocally($exception);
        else $this->rescueActionInPublic($exception);
    }
    
    private function rescueActionInPublic($exception)
    {
        if (in_array(get_class($exception), array('SRoutingException', 
            'SUnknownControllerException', 'SUnknownActionException')))
            $this->renderText(file_get_contents(ROOT_DIR.'/public/404.html'));
        else $this->renderText(file_get_contents(ROOT_DIR.'/public/500.html'));
    }
    
    private function rescueActionLocally($exception)
    {
        $this->assigns['exception']  = $exception;
        $this->assigns['controller_name'] = self::controllerClass($this->request->controller);
        $this->assigns['action_name']     = $this->actionName();
        $this->renderFile(ROOT_DIR.'/core/controller/lib/templates/rescue/exception.php');
    }
    
    private function logError($exception)
    {
        $this->logger->fatal(get_class($exception)." (".$exception->getMessage().")\n    "
        .implode("\n    ", $this->cleanBacktrace($exception))."\n");
    }
    
    private function cleanBacktrace($exception)
    {
        $trace = array();
        foreach ($exception->getTrace() as $t)
            $trace[] = $t['file'].':'.$t['line'].' in \''.$t['function'].'\'';
        return $trace;
    }
    
    private function pageCachePath($path)
    {
        return $this->pageCacheDir.$this->pageCacheFile($path);
    }
    
    private function pageCacheFile($path)
    {
        $name = ((empty($path) || $path == '/') ? '/index' : '/'.$path);
        $name.= $this->pageCacheExt;
        return $name;
    }
    
    private function isCachingAllowed()
    {
        return (!$this->request->isPost() && isset($this->response->headers['Status'])
            && $this->response->headers['Status'] < 400);
    }
    
    private static function dispatchWebServiceRequest($request, $response)
    {
        $protocol = $request->action;
        if (!in_array($protocol, array('xmlrpc')))
            throw new SUnknownProtocolException($protocol);
        $class = 'S'.$protocol.'Server';
        $server = new $class();
        list($method, $params) = $server->parseRequest($request->rawPostData());
        $parts = explode('.', $method);
        if (count($parts) < 2 || count($parts) > 3)
            throw new SException("Requested method does not exist : $method");
        $method = array_pop($parts);
        if (count($parts) == 2) $service = $parts[0].'/'.$parts[1];
        else $service = $parts[0];
        
        $wsRequest = new SWebServiceRequest($protocol, $service, $method, $params);
        $returnValue = self::invokeWebService($wsRequest);
        $response->body = $server->writeResponse($returnValue);
        return $response;
    }
    
    private static function invokeWebService($request)
    {
        if (file_exists(self::controllerFile('api')))
            return self::instanciateController('api')->invokeDelegatedWebService($request);
        else
            return self::instanciateController($request->service)->invokeDirectWebService($request);
    }
    
    private static function instanciateController($reqController)
    {
        if (!file_exists($path = self::controllerFile($reqController)))
    		throw new SUnknownControllerException(ucfirst($reqController).'Controller not found !');
    		
    	require_once($path);
    	
        $className = self::controllerClass($reqController);
		return new $className();
    }
    
    private static function controllerClass($reqController)
    {
        if (strpos($reqController, '/'))
    	   list( , $controllerName) = explode('/', $reqController);
    	else
    	   $controllerName = $reqController;
    	   
    	return SInflection::camelize($controllerName).'Controller';
    }
    
    private static function controllerFile($reqController)
	{
        return APP_DIR.'/controllers/'.$reqController.'_controller.php';
    }
}

?>
