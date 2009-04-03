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
class SActionController implements SIDispatchable, SIFilterable
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
    
    protected $hidden_actions  = array();
    
    protected $cached_pages   = array();
    protected $cached_actions = array();
    protected $page_cache_dir = null;
    protected $page_cache_ext = '.html';
    
    protected $before_filters;
    protected $after_filters;
    protected $around_filters;
    
    protected $module = null;
    
    private $performed_render   = false;
    private $performed_redirect = false;
    
    const DEFAULT_RENDER_STATUS_CODE = 200;
    
    public static $installed_modules = array();
    public static $session_handler = 'default';
    public static $fragment_cache_store = 'file';
    public static $file_store_path = '/cache/fragments';
    public static $memcache_hosts = array('localhost');
    public static $asset_host = null;
    public static $consider_all_requests_local = true;
    public static $perform_caching = true;
    public static $use_relative_urls = false;
    public static $template_class = 'SActionView';
    public static $exception_notifier = null;
    
    public static function instantiate($name, $module = null)
    {
        if (file_exists($path = STATO_APP_ROOT_PATH.'/app/controllers/application_controller.php'))
            require_once($path);
        
        $class_name = self::controller_class($name);
        if (!file_exists($path = self::controller_file($name, $module)))
    		throw new SUnknownControllerException("$class_name not found !");
    		
    	require_once($path);
		$controller = new $class_name();
        $controller->set_module($module);
        return $controller;
    }
    
    public function dispatch(SRequest $request, SResponse $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->params   =& $this->request->params;
        $this->assigns  =& $this->response->assigns;
        $this->session  = new SPhpSession();
        $this->flash    = new SFlash($this->session);
        $this->logger   = SLogger::get_instance();
        
        $this->page_cache_dir = STATO_APP_ROOT_PATH.'/public/cache';
        $this->initialize_template_class();
        
        $this->perform_action();
        
        return $this->response;
    }
    
    public function __construct()
    {
        $this->before_filters = new SFilterChain();
        $this->after_filters  = new SFilterChain();
        $this->around_filters = new SFilterChain();
    }
    
    public function __get($name)
    {
        if (isset($this->assigns[$name])) return $this->assigns[$name];
    }
    
    public function __set($name, $value)
    {
        $this->assigns[$name] = $value;
    }
    
    public function __isset($name)
    {
        return isset($this->assigns[$name]);
    }
    
    public function __unset($name)
    {
        unset($this->assigns[$name]);
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
    
    public function action_name()
    {
        if (!isset($this->request->params['action'])) return 'index';
        return $this->request->params['action'];
    }
    
    public function set_module($module)
    {
        $this->module = $module;   
    }
    
    public function url_for($options = array())
    {
        return SUrlRewriter::url_for($options);
    }
    
    public function process_to_log($request)
    {
        return array($this->controller_class_name(),
                     (!isset($request->params['action'])) ? 'index' : $request->params['action']);
    }
    
    /**
     * Overwrite to perform initializations prior to action call
     */
    protected function initialize()
    {
    
    }
    
    protected function render($status = null)
    {
        $this->render_action($this->action_name(), $status);
    }
    
    protected function render_xml($status = null)
    {
        $template = $this->template_path($this->controller_name(), $this->action_name(), $this->module);
        if (!file_exists($template)) throw new Exception('Template not found for this action');
        $this->response->headers['Content-Type'] = 'text/xml; charset=utf-8';
        $this->render_text('<?xml version="1.0" encoding="UTF-8"?'.">\n"
                           .$this->view->render($template), $status);
    }
    
    protected function render_action($action, $status = null)
    {
        $template = $this->template_path($this->controller_name(), $action, $this->module);
        if (!file_exists($template)) throw new SMissingTemplateException('Template not found for this action');
        
        if ($this->layout) $this->render_with_layout($template, $status);
        else $this->render_file($template, $status);
    }
    
    protected function render_with_layout($template, $status = null)
    {
        $this->add_variables_to_assigns();
        $this->assigns['layout_content'] = $this->view->render($template);
        
        $layout = $this->layout_path();
        if (!file_exists($layout)) throw new Exception('Layout not found');
        $this->render_file($layout, $status);
    }
    
    protected function render_file($path, $status = null)
    {
        $this->add_variables_to_assigns();
        $this->response->headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->render_text($this->view->render($path), $status);
    }
    
    protected function render_partial($partial, $local_assigns = array())
    {
        if (strpos($partial, '/') === false)
            $partial = $this->controller_name()."/_{$partial}.php";
        else {
            list($sub_path, $partial) = explode('/', $partial);
            $partial = "{$sub_path}/_{$partial}.php";
        }
        if ($this->module !== null)
            $path = STATO_APP_ROOT_PATH."/modules/{$this->module}/views/";
        else
            $path = STATO_APP_ROOT_PATH."/app/views/";
        
        $this->add_variables_to_assigns();
        $this->response->headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->render_text($this->view->render($path.$partial, $local_assigns), $status);
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
        $this->response->status = (!empty($status)) ? $status : self::DEFAULT_RENDER_STATUS_CODE;
        $this->response->body = $str;
    }
    
    public function render_nothing($status = null)
    {
        $this->render_text(' ', $status);
    }
    
    public function call_filter($method)
    {
        return $this->$method();
    }
    
    protected function template_path($controller, $action, $module = null)
    {
        if ($module !== null)
        {
            if (in_array($module, self::$installed_modules))
                return STATO_CORE_PATH."/modules/$module/views/$controller/$action.php";
                
            return STATO_APP_ROOT_PATH."/modules/$module/views/$controller/$action.php";
        }
        return STATO_APP_PATH."/views/$controller/$action.php";
    }
    
    protected function layout_path()
    {
        if (strpos($this->layout, '/') !== false)
        {
            list($module, $layout) = explode('/', $this->layout);
            if (in_array($module, self::$installed_modules))
                return STATO_CORE_PATH."/modules/$module/views/layouts/$layout.php";
            
            return STATO_APP_ROOT_PATH."/modules/$module/views/layouts/$layout.php";
        }
        return STATO_APP_PATH."/views/layouts/{$this->layout}.php";
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
                so redirect_back() could not be called successfully');
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
        $this->response->status = self::DEFAULT_RENDER_STATUS_CODE;
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
        
        if (!isset($params['length']) && !is_resource($data)) $params['length'] = strlen($data);
        
        $this->send_file_headers($params);
        
        if (is_resource($data))
        {
            $this->response->send_headers();
            rewind($data);
            fpassthru($data);
            exit();
        }
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
            return;
        }
        else $this->render_text(file_get_contents($path));
    }
    
    protected function cache_page($content = null, $options = array())
    {
        if (!self::$perform_caching) return;
        
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
        if (!self::$perform_caching) return;
        
        if (is_array($options))
            $path = $this->url_for(array_merge($options, array('only_path' => true, 'skip_relative_url_root' => true)));
        else 
            $path = $options;
            
        if (file_exists($this->page_cache_path($path))) @unlink($this->page_cache_path($path));
    }
    
    protected function expire_fragment($id)
    {
        $this->view->expire_fragment($id);
    }
    
    protected function expire_cache($relative_dir = null)
    {
        if (!self::$perform_caching) return;
        
        $cache_dir = $this->page_cache_dir;
        if ($relative_dir !== null) $cache_dir.= $relative_dir;
        
        if (is_dir($cache_dir)) SDir::rmdirs($cache_dir);
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
    
    private function perform_action()
    {
        $action = $this->action_name();
        if (!$this->action_exists($action))
            throw new SUnknownActionException("Action $action not found in ".$this->controller_class_name());
        
        $this->initialize();
        $this->require_dependencies();
        
        $this->session->start();
        
        if (!empty($this->cached_actions) && self::$perform_caching)
            $this->around_filters->append(new SActionCacheFilter($this->cached_actions));
        
        $before_result = $this->before_filters->process($this, $this->action_name(), 'before');
        $this->around_filters->process($this, $this->action_name(), 'before');
        
        if ($before_result !== false && !$this->is_performed())
        {
            $this->$action();
            if (!$this->is_performed()) $this->render();
        }
        
        $this->around_filters->process($this, $this->action_name(), 'after');
        $this->after_filters->process($this, $this->action_name(), 'after');
        
        $this->session->store();
        
        if (in_array($this->action_name(), $this->cached_pages) && self::$perform_caching && $this->is_caching_allowed())
            $this->cache_page($this->response->body, array('action' => $this->action_name(), 'params' => $this->params));
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
        try {
            SDependencies::require_model($this->controller_name());
        } catch (SDependencyNotFound $e) {};
        
        try {
            SDependencies::require_helper($this->controller_name(), $this->module);
        } catch (SDependencyNotFound $e) {};
        
        SDependencies::require_models($this->models);
        SDependencies::require_helpers($this->helpers, $this->module);
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
    
    private static function controller_class($req_controller)
    { 
    	return SInflection::camelize($req_controller).'Controller';
    }
    
    private static function controller_file($req_controller, $module)
    {
        if ($module === null)    
            return STATO_APP_ROOT_PATH."/app/controllers/{$req_controller}_controller.php";
            
        if (in_array($module, self::$installed_modules)) $base_path = STATO_CORE_PATH.'/modules';
        else $base_path = STATO_APP_ROOT_PATH.'/modules';
        
        $base_controller_path = "{$base_path}/{$module}/controllers/base_controller.php";
        $controller_path = "{$base_path}/{$module}/controllers/{$req_controller}_controller.php";
        
        if (file_exists($base_controller_path)) require_once($base_controller_path);
            
        return $controller_path;
    }
}

?>
