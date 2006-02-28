<?php

require_once(ROOT_DIR.'/core/model/model.php');
require_once(ROOT_DIR.'/core/view/view.php');

class SActionController
{   
    public $request  = null;
    public $session  = null;
    public $response = null;
    public $view     = null;
    public $flash    = null;
    
    public $layout      = false;
    public $useModels   = array();
    public $useHelpers  = array();
    
    public $cachePages     = array();
    public $cacheActions   = array();
    public $pageCacheDir   = null;
    public $pageCacheExt   = '.html';
    public $performCaching = True;
    
    public $beforeFilters = array();
    public $afterFilters  = array();
    public $autoCompleteFor = array();
    
    protected $assigns = array();
    
    protected $virtualMethods    = array();
    protected $performedRender   = false;
    protected $performedRedirect = false;
    
    protected static $defaultRenderStatusCode = '200 OK';
    
    public function __construct()
    {
        $this->view    = new SActionView($this);
        $this->session = new SSession();
        $this->flash   = new SFlash($this->session);
        
        $this->pageCacheDir = ROOT_DIR.'/public/cache';
        
        /*foreach($this->autoCompleteFor as $params)
        {
            $method = 'autoCompleteFor'.ucfirst($params[0]).ucfirst($params[1]);
            $this->virtualMethods[$method] = array('autoCompleteFor', $params);
        }*/
    }
    
    public function process($request, $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->params   = $this->request->params;
        
        SLocale::loadStrings($this->inclusionPath().'/i18n/');
        
        foreach($this->useModels as $model) $this->requireModel($model);
        foreach($this->useHelpers as $helper) $this->requireHelper($helper);
        
        $this->performAction($this->actionName());
        
        return $this->response;
    }
    
    public function __get($name)
    {
        if (isset($this->assigns[$name])) return $this->assigns[$name];
    }
    
    public function __set($name, $value)
    {
        $this->assigns[$name] = $value;
    }
    
    public function __call($action, $args)
    {
        if (in_array($action, array_keys($this->virtualMethods)))
        {
            $method = $this->virtualMethods[$action][0];
            $params = $this->virtualMethods[$action][1];
            $this->$method($params);
        }
    }
    
    public function actionExists($action)
    {
        try
        {
            $method = new ReflectionMethod(get_class($this), $action);
            if ($method->isPublic() && !$method->isConstructor()
                && $method->getDeclaringClass()->getName() != __CLASS__)
                return true;
            else
                return false;
        }
        catch (ReflectionException $e)
        {
            if (in_array($action, array_keys($this->virtualMethods)))
                return true;
            else
                return false;
        }
    }
    
    public function performAction($action)
    {
        foreach($this->beforeFilters as $method) $this->$method();
        $this->$action();
        foreach($this->afterFilters as $method) $this->$method();
        if (!$this->isPerformed()) $this->render();
    }
    
    public function urlFor($options)
    {
        if (!isset($options['action']))     $options['action'] = 'index';
        if (!isset($options['controller'])) $options['controller'] = $this->controllerName();
        if (!isset($options['module']))     $options['module'] = $this->request->module;
        
        return SRoutes::rewriteUrl($options, $this->request);
    }
    
    public function controllerName()
    {
        return str_replace('controller', '', strtolower(get_class($this)));
    }
    
    public function actionName()
    {
        if (empty($this->request->action)) return 'index';
        return $this->request->action;
    }
    
    protected function render()
    {
        $this->renderAction($this->actionName());
    }
    
    protected function renderText($str)
    {
        $this->performedRender = true;
        $this->response->header['Status'] = self::$defaultRenderStatusCode;
        $this->response->header['Content-Type'] = 'text/html; charset=utf-8';
        $this->response->body = $str;
    }
    
    protected function renderFile($path)
    {
        $this->addVariablesToAssigns();
        $this->renderText($this->view->render($path, $this->assigns));
    }
    
    protected function renderAction($action)
    {
        $template = $this->templatePath($this->request->module, $this->controllerName(), $action);
        if (!file_exists($template)) throw new SException('Template not found for this action');
        
        if ($this->layout) $this->renderWithLayout($template);
        else $this->renderFile($template);
    }
    
    protected function renderWithLayout($template)
    {
        $this->addVariablesToAssigns();
        $this->assigns['layout_content'] = $this->view->render($template, $this->assigns);
        
        $layout = APP_DIR.'/layouts/'.$this->layout.'.php';
        if (!file_exists($layout)) throw new SException('Layout not found');
        $this->renderFile($layout);
    }
    
    protected function addVariablesToAssigns()
    {
        if (!$this->flash->isEmpty()) $this->assigns['flash'] = $this->flash->dump();
        $this->flash->discard();
    }
    
    protected function templatePath($module, $controller, $action)
    {
        return $this->inclusionPath($module)."/views/$controller/$action.php";
    }
    
    protected function redirect($urlOptions)
    {
        $this->performedRedirect = true;
        if (!is_array($urlOptions))
        {
            $options = array();
            $options['action'] = $urlOptions;
        }
        else $options = $urlOptions;
        $this->response->redirectedTo = $options;
        $this->response->redirect($this->urlFor($options));
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
        $this->response->header['Status'] = self::$defaultRenderStatusCode;
        unset($this->response->header['location']);
    }
    
    protected function sendFile($path, $params=array())
    {
        $fp = @fopen($path, "rb");
        if ($fp)
        {
            if (isset($params['type'])) 
                header("Content-Type: ".$params['type']);
            if (isset($params['disposition'])) 
                header("Content-disposition: ".$params['disposition']);
            fpassthru($fp);
            exit();
        }
        else
        {
            throw new SException('File not found : '.$path);
        }
    }
    
    protected function paginate($className, $perPage=10, $options=array())
    {
        $paginator = new SPaginator($className, $perPage, $options);
        return array($paginator, $paginator->currentPage());
    }
    
    protected function rescueAction($exception)
    {
        if ($this->isPerformed()) $this->eraseResults();
        
        if (APP_MODE == 'prod') $this->rescueActionLocally($exception);
        else $this->rescueActionInPublic($exception);
    }
    
    protected function rescueActionInPublic($exception)
    {
        // selon le type d'exception, 404 ou 500
        $this->renderText(file_get_contents(ROOT_DIR.'/public/404.html'));
        // $this->renderText(file_get_contents(ROOT_DIR.'/public/500.html'));
    }
    
    protected function rescueActionLocally($exception)
    {
        $this->assigns['exception']  = $exception;
        $this->assigns['controller'] = ucfirst($this->controllerName()).'Controller';
        $this->assigns['action']     = $this->actionName();
        $this->renderFile(ROOT_DIR.'/core/view/templates/rescue/exception.php');
    }
    
    /*protected function autoCompleteFor($args)
    {
        $object = $args[0];
        $method = $args[1];
        if (!isset($args[2])) $options = array();
        else $options = $args[2];
        
        $condition = "LOWER({$method}) LIKE '%".strtolower($this->params[$object][$method])."%'";
        $options = array_merge(array('order' => "{$method} ASC", 'limit' => 10), $options);
        $entities = $this->$object->findAll($condition, $options);
        $items = '';
        foreach($entities as $entity) $items.= "<li>{$entity->$method}</li>";
        $this->renderText("<ul>{$items}</ul>");
    }*/
    
    private function isPerformed()
    {
        return ($this->performedRender || $this->performedRedirect);
    }
    
    private function requireModel($model)
    {
        if (class_exists($model)) return;
        
        if (!strpos($model, '/'))
            $module = $this->request->module;
        else
            list($module, $model) = explode('/', $model);
        
        $file = $this->inclusionPath($module).'/models/'.strtolower($model).'.class.php';
        if (!file_exists($file)) throw new SException('Model not found : '.$model);
        require_once($file);
    }
    
    private function requireHelper($helper)
    {
        if (!strpos($helper, '/'))
            $module = $this->request->module;
        else
            list($module, $helper) = explode('/', $helper);
        
        $file = $this->inclusionPath($module)."/helpers/{$helper}helper.lib.php";
        if (!file_exists($file)) throw new SException('Helper not found : '.$helper);
        require_once($file);
    }
    
    private function inclusionPath($module = Null)
    {
        if ($module == Null) $module = $this->request->module;
        if ($module == 'root') return APP_DIR;
        return APP_DIR."/modules/$module";
    }
}

?>
