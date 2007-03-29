<?php

class SUnknownControllerException extends Exception {}
class SUnknownActionException extends Exception {}
class SUnknownProtocolException extends Exception {}
class SUnknownServiceException extends Exception {}
class SDoubleRenderException extends Exception {}
class SHttp404 extends Exception {}

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
 *         $this->posts = Post::$objects->filter(...);
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
     * Examples : <var>$this->request->is_post(); $this->request->request_uri();</var>     
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
    
    protected $layout     = false;
    protected $models     = array();
    protected $helpers    = array();
    protected $components = array();
    
    protected $hidden_actions  = array();
    
    protected $cached_pages   = array();
    protected $cached_actions = array();
    protected $page_cache_dir = null;
    protected $page_cache_ext = '.html';
    
    protected $before_filters = array();
    protected $after_filters  = array();
    protected $around_filters = array();
    
    protected $skip_before_filters = array();
    protected $skip_after_filters  = array();
    
    protected $web_services = array();
    
    private $sub_directory      = null;
    
    private $performed_render   = false;
    private $performed_redirect = false;
    
    public $parent_controller  = null;
    
    const DEFAULT_RENDER_STATUS_CODE = '200 OK';
    
    public static $session_store = 'php';
    public static $consider_all_requests_local = true;
    public static $perform_caching = true;
    public static $template_class = 'SActionView';
    
    
    public static function factory($request, $response)
    {
        return self::instanciate_controller($request->controller)->process($request, $response);
    }
    
    public static function process_with_exception($request, $response, $exception)
    {
        $controller = new SActionController();
        return $controller->process($request, $response, 'rescue_action', $exception);
    }
    
    public static function process_with_component($class, $request, $response, $parent_controller = null)
    {
        $controller = new $class();
        $controller->parent_controller = $parent_controller;
        return $controller->process($request, $response);
    }
    
    public function process($request, $response, $method = 'perform_action', $arguments = null)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->params   =& $this->request->params;
        $this->assigns  =& $this->response->assigns;
        
        if ($arguments != null) $this->$method($arguments);
        else $this->$method();
        
        return $this->response;
    }
    
    public function __construct()
    {
        $this->initialize_template_class();
        $this->logger = SLogger::get_instance();
        $this->page_cache_dir = STATO_APP_ROOT_PATH.'/public/cache';
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
    public function controller_name()
    {
        return SInflection::underscore(str_replace('Controller', '', $this->controller_class_name()));
    }
    
    /**
     * Returns the class name
     */
    public function controller_class_name()
    {
        return get_class($this);
    }
    
    public function controller_path()
    {
        return SDependencies::sub_directory(get_class($this)).$this->controller_name();
    }
    
    public function action_name()
    {
        if (empty($this->request->action)) return 'index';
        return $this->request->action;
    }
    
    public function url_for($options = array())
    {
        if (!isset($options['controller']))
        {
            $options['controller'] = $this->controller_path();
            if (!isset($options['action'])) $options['action'] = $this->action_name();
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
        $this->render_action($this->action_name(), $status, $xml);
    }
    
    protected function render_update($local_assigns = array())
    {
        $template = $this->pjs_path($this->controller_path(), $this->action_name());
        if (!file_exists($template)) throw new Exception('PJS file not found for this action');
        $this->response->headers['Content-Type'] = 'text/javascript; charset=UTF-8';
        $this->render_text($this->view->render_update($template, $local_assigns));
    }
    
    protected function render_xml($status = null)
    {
        $template = $this->template_path($this->controller_path(), $this->action_name());
        if (!file_exists($template)) throw new Exception('Template not found for this action');
        $this->response->headers['Content-Type'] = 'text/xml; charset=utf-8';
        $this->render_text('<?xml version="1.0" encoding="UTF-8"?'.">\n"
                           .$this->view->render($template), $status);
    }
    
    protected function render_action($action, $status = null)
    {
        $template = $this->template_path($this->controller_path(), $action);
        if (!file_exists($template)) throw new SMissingTemplateException('Template not found for this action');
        
        if ($this->layout) $this->render_with_layout($template, $status);
        else $this->render_file($template, $status);
    }
    
    protected function render_with_layout($template, $status = null)
    {
        $this->add_variables_to_assigns();
        $this->assigns['layout_content'] = $this->view->render($template);
        
        $layout = STATO_APP_PATH.'/views/layouts/'.$this->layout.'.php';
        if (!file_exists($layout)) throw new Exception('Layout not found');
        $this->render_file($layout, $status);
    }
    
    protected function render_file($path, $status = null)
    {
        $this->add_variables_to_assigns();
        $this->response->headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->render_text($this->view->render($path), $status);
    }
    
    protected function render_component($options = array())
    {
        $this->response->headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->render_text($this->component_response($options, true)->body); 
    }
    
    protected function render_partial($partial, $local_assigns = array())
    {
        $this->add_variables_to_assigns();
        if (strpos($partial, '/') === false) $partial = $this->controller_path().'/'.$partial;
        $this->response->headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->render_text($this->view->render_partial($partial, $local_assigns));
    }
    
    /**
     * Renders text without the active layout. 
     * 
     * It is usually used for rendering prepared content by renderFile() and 
     * Action Caching, but you can use it for tests.    
     * Note that it must remain public instead of protected because SActionCacheFilter
     * must be able to call it directly.
     */
    public function render_text($str, $status = null)
    {
        if ($this->is_performed())
            throw new SDoubleRenderException('Can only render or redirect once per action');
        
        $this->performed_render = true;
        $this->response->headers['Status'] = (!empty($status)) ? $status : self::DEFAULT_RENDER_STATUS_CODE;
        $this->response->body = $str;
    }
    
    public function render_nothing($status = null)
    {
        $this->render_text(' ', $status);
    }
    
    protected function template_path($controller_path, $action)
    {
        return STATO_APP_PATH."/views/$controller_path/$action.php";
    }
    
    protected function pjs_path($controller_path, $action)
    {
        return STATO_APP_PATH."/views/$controller_path/$action.pjs";
    }
    
    protected function add_variables_to_assigns()
    {
        $this->assigns['params'] = $this->params;
        $this->assigns['request'] = $this->request;
        if (isset($this->flash) && isset($this->session))
        {
            if (!$this->flash->is_empty()) $this->assigns['flash'] = $this->flash->dump();
            $this->flash->discard();
            $this->assigns['session'] = $this->session;
        }
    }
    
    protected function redirect_to($options)
    {
        if (is_array($options))
        {
            $this->redirect_to($this->url_for($options));
            //$this->response->redirected_to = $options;
        }
        elseif (preg_match('#^\w+://.*#', $options))
        {
            if ($this->is_performed())
                throw new SDoubleRenderException('Can only render or redirect once per action');
            
            $this->logger->info("Redirected to {$options}");
            $this->response->redirect($options);
            $this->response->redirected_to = $options;
            $this->performed_redirect = true;
        }
        else
        {
            $this->redirect_to($this->request->protocol().$this->request->host_with_port().$options);
        }
    }
    
    protected function redirect_back()
    {
        if (isset($_SERVER['HTTP_REFERER'])) $this->redirect_to($_SERVER['HTTP_REFERER']);
        else
        {
            throw new Exception('No HTTP_REFERER was set in the request to this action, 
                so redirectBack() could not be called successfully');
        }
    }
    
    protected function expires_in($seconds, $options = array())
    {
        $cache_options = array_merge(array('max-age' => $seconds, 'private' => true), $options);
        $cache_control = array();
        foreach ($cache_options as $k => $v)
        {
            if ($v === false || $v === null) unset($cache_options[$k]);
            if ($v === true) $cache_control[] = $k;
            else $cache_control[] = "$k=$v";
        }
        $this->response->headers['Cache-Control'] = implode(',', $cache_control);
    }
    
    protected function expires_now()
    {
        $this->response->headers['Cache-Control'] = 'no-cache';
    }
    
    protected function erase_results()
    {
        $this->erase_render_results();
        $this->erase_redirect_results();
    }
    
    protected function erase_render_results()
    {
        $this->response->body = '';
        $this->performed_render = false;
    }
    
    protected function erase_redirect_results()
    {
        $this->performed_redirect = false;
        $this->response->redirected_to = null;
        $this->response->headers['Status'] = self::DEFAULT_RENDER_STATUS_CODE;
        unset($this->response->headers['location']);
    }
    
    protected function send_data($data, $params=array())
    {
        $defaults = array
        (
            'type' => 'application/octet-stream',
            'disposition' => 'attachment',
        );
        $params = array_merge($defaults, $params);
        
        if (!isset($params['length'])) $params['length'] = strlen($data);
        
        $this->send_file_headers($params);
        
        $this->render_text($data);
    }
    
    protected function send_file($path, $params=array())
    {
        if (!file_exists($path) || !is_readable($path)) 
            throw new Exception('Cannot read file : '.$path);
        
        $defaults = array
        (
            'type' => 'application/octet-stream',
            'disposition' => 'attachment',
            'stream' => true
        );
        $params = array_merge($defaults, $params);
        
        if (!isset($params['filename'])) $params['filename'] = basename($path);
        if (!isset($params['length']))   $params['length']   = filesize($path);
        
        $this->send_file_headers($params);
        
        if ($params['stream'] === true)
        {
            $this->response->send_headers();
            $fp = @fopen($path, "rb");
            fpassthru($fp);
            exit();
        }
        else $this->render_text(file_get_contents($path));
    }
    
    protected function cache_page($content = null, $options = array())
    {
        if (!$this->perform_caching) return;
        
        if ($content == null) $content = $this->response->body;
        if (is_array($options))
            $path = $this->url_for(array_merge($options, array('only_path' => true, 'skip_relative_url_root' => true)));
        else 
            $path = $options;
        
        if (!SDir::mkdirs(dirname($this->page_cache_path($path)), 0700, true))
            throw new Exception('Caching failed with dirs creation');
        file_put_contents($this->page_cache_path($path), $content);
    }
    
    protected function expire_page($options = array())
    {
        if (!$this->perform_caching) return;
        
        if (is_array($options))
            $path = $this->url_for(array_merge($options, array('only_path' => true, 'skip_relative_url_root' => true)));
        else 
            $path = $options;
            
        if (file_exists($this->page_cache_path($path))) unlink($this->page_cache_path($path));
    }
    
    protected function expire_fragment($id)
    {
        if (!$this->perform_caching) return;
        
        if (is_array($id))
            list($protocol, $id) = explode('://', $this->url_for($id));
        
        $file = STATO_APP_ROOT_PATH."/cache/fragments/{$id}";
        if (file_exists($file)) unlink($file);
    }
    
    protected function paginate($query_set, $per_page=10, $param='page')
    {
        if (isset($this->request->params[$param]))
            $current_page = $this->request->params[$param];
        else
            $current_page = 1;
        
        $paginator = new SPaginator($query_set, $per_page, $current_page, $param);
        return array($paginator, $paginator->current_page());
    }
    
    protected function add_web_service($name, $instance)
    {
        $this->web_services[$name] = $instance;
    }
    
    protected function invoke_web_service($protocol)
    {
        if (!in_array($protocol, array('xmlrpc')))
            throw new SUnknownProtocolException($protocol);
            
        $class = 'S'.$protocol.'Server';
        $server = new $class();
        
        try {
            $ws_request = $server->parse_request($this->request->raw_post_data());
        
            if (!array_key_exists($ws_request->service, $this->web_services))
                throw new SUnknownServiceException();
            
            $return_value = $this->web_services[$ws_request->service]->invoke($ws_request);
            $raw_response = $server->write_response($return_value);
        }
        catch (SWebServiceFault $fault) {
            $raw_response = $server->write_fault($fault->getMessage(), $fault->getCode());
        }
        catch (Exception $e) {
            $raw_response = $server->write_fault('Internal server error', 500);
            $this->log_error($e);
        }
        
        $this->send_data($raw_response, array('type' => 'text/xml', 'disposition' => 'inline'));
    }
    
    protected function log_processing()
    {
        $log = "\n\nProcessing ".$this->controller_class_name().'::'.$this->action_name()
            .'() for '.$this->request->remote_ip().' at '
            .SDateTime::today()->__toString().' ['.$this->request->method().']';
        if (($sess_id = $this->session->session_id()) != '') $log.= "\n    Session ID: ".$sess_id;
        $log.= "\n    Parameters: ".serialize($this->params);
        $this->logger->info($log);
    }
    
    protected function log_benchmarking()
    {
        $runtime = microtime(true) - STATO_TIME_START;
        $info = 'Completed in '.sprintf("%.5f", $runtime).' seconds';
        if (class_exists('SActiveRecord', false))
        {
            $db_runtime = SActiveRecord::connection_benchmark();
            $db_percentage = ($db_runtime * 100) / $runtime;
            $info.= ' | DB: '.sprintf("%.5f", $db_runtime).' ('.sprintf("%d", $db_percentage).' %)';
        }
        $this->logger->info($info);
        
        if (class_exists('SActiveRecord', false) && SActiveRecord::$log_sql === true)
            SActiveRecord::connection()->write_log();
    }
    
    protected function rescue_action($exception)
    {
        if ($this->is_performed()) $this->erase_results();
        $this->log_error($exception);
        if (self::$consider_all_requests_local) $this->rescue_action_locally($exception);
        else $this->rescue_action_in_public($exception);
    }
    
    private function perform_action()
    {
        $action = $this->action_name();
        if (!$this->action_exists($action))
            throw new SUnknownActionException("Action $action not found in ".$this->controller_class_name());
        
        $this->initialize();
        $this->require_dependencies();
        
        $session_store_class = 'S'.ucfirst(self::$session_store).'Session';
        $this->session = new $session_store_class();
        $this->flash   = new SFlash($this->session);
        
        $this->log_processing();
        
        if (!empty($this->cached_actions))
            $this->around_filters[] = new SActionCacheFilter($this->cached_actions);
        
        $before_result = $this->process_filters('before');
        foreach($this->around_filters as $filter) $filter->before($this);
        
        if ($before_result !== false && !$this->is_performed())
        {
            $this->$action();
            if (!$this->is_performed()) $this->render();
        }
        
        foreach($this->around_filters as $filter) $filter->after($this);
        $this->process_filters('after');
        
        $this->session->store();
        
        if (in_array($this->action_name(), $this->cached_pages) && $this->perform_caching && $this->is_caching_allowed())
            $this->cache_page($this->response->body, array('action' => $this->action_name(), 'params' => $this->params));
        
        $this->log_benchmarking();
    }
    
    private function action_exists($action)
    {
        try
        {
            $method = new ReflectionMethod(get_class($this), $action);
            return ($method->isPublic() && !$method->isConstructor()
                    && $method->getDeclaringClass()->getName() != __CLASS__
                    && !in_array($action, $this->hidden_actions));
        }
        catch (ReflectionException $e)
        {
             return false;
        }
    }
    
    private function require_dependencies()
    {
        SLocale::load_strings(STATO_APP_PATH.'/i18n/'.SDependencies::sub_directory(get_class($this)));
        SUrlRewriter::initialize($this->request);
        
        try
        {
            SDependencies::require_dependency('models', SInflection::singularize($this->controller_name()), get_class($this));
            SDependencies::require_dependency('helpers', $this->controller_name().'Helper', get_class($this));
        }
        catch (SDependencyNotFound $e) {}
        
        foreach($this->helpers as $k => $helper) $this->helpers[$k] = $helper.'Helper';
        
        SDependencies::require_dependencies('models', $this->models, get_class($this));
        SDependencies::require_dependencies('helpers', $this->helpers, get_class($this));
        
        if (!empty($this->components))
            SDependencies::require_components($this->components);
    }
    
    private function component_response($options, $reuse_response)
    {
        $controller = $options['controller'];
        $class = SInflection::camelize($controller).'Controller';
        
        if (!file_exists($path = STATO_APP_PATH."/components/{$controller}/{$controller}_controller.php"))
    		throw new SUnknownControllerException(ucfirst($req_controller).' Component not found !');
    		
    	require_once($path);
        
        $request = $this->request_for_component($options);
        $response = ($reuse_response) ? $this->response : new SResponse();
        return SActionController::process_with_component($class, $request, $response, $this);
    }
    
    private function request_for_component($options)
    {
        $request = clone $this->request;
        $request->params = array_merge($request->params, $options);
        return $request;
    }
    
    private function process_filters($state)
    {
        $prop = $state.'_filters';
        foreach ($this->$prop as $filter)
        {
            if (is_array($filter))
            {
                $method = $filter[0];
                
                if (isset($filter['only']) && !is_array($filter['only']))
                    $filter['only'] = array($filter['only']);
                if (isset($filter['except']) && !is_array($filter['except']))
                    $filter['except'] = array($filter['except']);
                
                if ((isset($filter['only']) && in_array($this->action_name(), $filter['only']))
                    || (isset($filter['except']) && !in_array($this->action_name(), $filter['except']))
                    || (!isset($filter['only']) && !isset($filter['except'])))
                    $result = $this->call_filter($method, $state);
            }
            else $result = $this->call_filter($filter, $state);
            
            if ($result === false) return false;
        }
    }
    
    private function call_filter($method, $state)
    {
        $skip_prop = 'skip_'.$state.'_filters';
        if (!in_array($method, $this->$skip_prop)) return $this->$method();
    }
    
    private function is_performed()
    {
        return ($this->performed_render || $this->performed_redirect);
    }
    
    private function send_file_headers($params = array())
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
    
    private function rescue_action_in_public($exception)
    {
        $status = $this->response_code_for_rescue($exception);
        if (in_array(get_class($exception), array('SHttp404', 'SRoutingException', 
            'SUnknownControllerException', 'SUnknownActionException')))
            $this->render_text(file_get_contents(STATO_APP_ROOT_PATH.'/public/404.html'), $status);
        else $this->render_text(file_get_contents(STATO_APP_ROOT_PATH.'/public/500.html'), $status);
    }
    
    private function rescue_action_locally($exception)
    {
        $rescue_path = STATO_CORE_PATH.'/controller/lib/templates/rescue/';
        $this->add_variables_to_assigns();
        $this->assigns['exception'] = $exception;
        $this->assigns['controller_name'] = self::controller_class($this->request->controller);
        $this->assigns['action_name'] = $this->action_name();
        $this->assigns['layout_content'] 
            = $this->view->render($rescue_path.$this->template_file_for_local_rescue($exception).'.php');
        
        $this->render_file($rescue_path.'layout.php', $this->response_code_for_rescue($exception));
    }
    
    private function template_file_for_local_rescue($exception)
    {
        switch (get_class($exception))
        {
            case 'SRoutingException':
                return 'routing_error';
            case 'SMissingTemplateException':
                return 'template_missing';
            case 'SUnknownControllerException':
                return 'unknown_controller';
            case 'SUnknownActionException':
                return 'unknown_action';
            default:
                return 'diagnostics';
        }
    }
    
    private function response_code_for_rescue($exception)
    {
        if (in_array(get_class($exception), array('SRoutingException', 
            'SUnknownControllerException', 'SUnknownActionException')))
            return '404 Page Not Found';
        else
            return '500 Internal Error';
    }
    
    private function log_error($exception)
    {
        $this->logger->fatal(get_class($exception)." (".$exception->getMessage().")\n    "
        .implode("\n    ", $this->clean_backtrace($exception))."\n");
    }
    
    private function clean_backtrace($exception)
    {
        foreach ($exception->getTrace() as $t)
        {
            $str = '';
            if (isset($t['file']) && isset($t['line'])) $str.= $t['file'].':'.$t['line'];
            else $str.= 'undefined';
            if (isset($t['class'])) $str.= ' in \''.$t['class'].$t['type'].$t['function'].'\'';
            else $str.= ' in \''.$t['function'].'\'';
            $trace [] = $str;
        }
        return $trace;
    }
    
    private function page_cache_path($path)
    {
        return $this->page_cache_dir.$this->page_cache_file($path);
    }
    
    private function page_cache_file($path)
    {
        $name = ((empty($path) || $path == '/') ? '/index' : '/'.$path);
        $name.= $this->page_cache_ext;
        return $name;
    }
    
    private function is_caching_allowed()
    {
        return (!$this->request->is_post() && isset($this->response->headers['Status'])
            && $this->response->headers['Status'] < 400);
    }
    
    private function initialize_template_class()
    {
        $view_class = self::$template_class;
        $this->view = new $view_class($this);
    }
    
    private static function instanciate_controller($req_controller)
    {
        $class_name = self::controller_class($req_controller);
        if (!file_exists($path = self::controller_file($req_controller)))
    		throw new SUnknownControllerException(SInflection::camelize($req_controller).'Controller not found !');
    		
    	require_once($path);
		return new $class_name();
    }
    
    private static function controller_class($req_controller)
    {
        if (strpos($req_controller, '/') === false) $controller_name = $req_controller; 
    	else
    	{
            list($module, $controller_name) = explode('/', $req_controller);
            if (file_exists($path = STATO_APP_PATH.'/controllers/'.$module.'/base_controller.php'))
                require_once($path);
        }
    	   
    	return SInflection::camelize($controller_name).'Controller';
    }
    
    private static function controller_file($req_controller)
	{
        return STATO_APP_PATH.'/controllers/'.$req_controller.'_controller.php';
    }
}

?>
