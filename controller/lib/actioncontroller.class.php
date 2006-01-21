<?php

require_once(ROOT_DIR.'/core/model/model.php');
require_once(ROOT_DIR.'/core/view/view.php');

/**
 * ActionController
 * 
 * @package 
 * @author goldoraf
 * @copyright Copyright (c) 2005
 * @version 0.1
 * @access public
 **/
class ActionController
{   
    public $request  = null;
    public $session  = null;
    public $response = null;
    public $flash    = null;
    
    public $layout      = false;
    public $useModels   = array();
    public $useHelpers  = array();
    
    public $beforeFilters = array();
    public $afterFilters = array();
    public $autoCompleteFor = array();
    
    protected $virtualMethods = array();
    
    public function __construct()
    {
        $this->request  = Context::$request;
        $this->session  = Context::$session;
        $this->response = Context::$response;
        
        $this->flash = new Flash();
        
        $this->params = $this->request->params;
        
        foreach($this->autoCompleteFor as $params)
        {
            $method = 'autoCompleteFor'.ucfirst($params[0]).ucfirst($params[1]);
            $this->virtualMethods[$method] = array('autoCompleteFor', $params);
        }
        
        foreach($this->useModels as $model) $this->requireModel($model);
    }
    
    public function __get($name)
    {
        if (isset($this->response[$name])) return $this->response[$name];
    }
    
    public function __set($name, $value)
    {
        $this->response[$name] = $value;
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
    
    public function callAction($action)
    {
        foreach($this->beforeFilters as $method) $this->$method();
        $this->$action();
        foreach($this->afterFilters as $method) $this->$method();
    }
    
    public function render()
    {
        $path = $this->defaultTemplatePath();
        if (!file_exists($path)) throw new Exception('Template not found for this action');
        $this->renderFile($path);
    }
    
    public function renderText($str)
    {
        header("Content-Type: text/html; charset=utf-8");
        echo $str;
        exit();
    }
    
    public function renderFile($path)
    {
        if (!$this->flash->isEmpty()) $this->response['flash'] = $this->flash->dump();
        $this->flash->discard();
        
        foreach($this->useHelpers as $helper) $this->requireHelper($helper);
        
        $renderer = new Renderer($path, $this->response->values);
        if ($this->layout)
        {
            $layout = APP_DIR.'/layouts/'.$this->layout.'.php';
            if (!file_exists($layout)) throw new Exception('Layout not found');
            $this->response['layoutContent'] = $renderer->render();
            $renderer = new Renderer($layout, $this->response->values);
        }
        $this->renderText($renderer->render());
    }
    
    protected function renderAction($action)
    {
        $this->renderFile($this->templatePath($this->request->module,
                                              $this->request->controller,
                                              $action));
    }
    
    protected function redirect($urlOptions)
    {
        if (!is_array($urlOptions))
        {
            $options = array();
            $options['action'] = $urlOptions;
        }
        else $options = $urlOptions;
        
        if (!isset($options['controller'])) $options['controller'] = $this->getName();
        if (!isset($options['module'])) $options['module'] = $this->request->module;
        if (!isset($options['action'])) $options['action'] = 'index';
        
        $this->redirectToPath(Routes::rewriteUrl($options));
    }
    
    protected function redirectToPath($path)
    {
        header('location:'.$path);
        exit();
    }
    
    protected function sendFile($path, $params=array())
    {
        $fp = fopen($path, "rb");
        if ($fp)
        {
            if (isset($params['type'])) 
                header("Content-Type: ".$params['type']);
            if (isset($params['disposition'])) 
                header("Content-disposition: ".$params['disposition']);
            fpassthru($fp);
            exit();
        }
        // else ?
    }
    
    protected function getName()
    {
        return strtolower(str_replace('Controller', '', get_class($this)));
    }
    
    protected function paginate($entityClass, $perPage=10, $options=array())
    {
        $paginator = new Paginator($entityClass, $perPage, $options);
        return array($paginator, $paginator->currentPage());
    }
    
    protected function autoCompleteFor($args)
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
    }
    
    protected function defaultTemplatePath()
    {
        $module     = $this->request->module;
        $controller = $this->request->controller;
        $action     = $this->request->action;
        return $this->templatePath($module, $controller, $action);
    }
    
    protected function templatePath($module, $controller, $action)
    {
        return Context::inclusionPath($module)."/views/$controller/$action.php";
    }
    
    protected function requireModel($model)
    {
        if (class_exists($model)) return;
        $file = Context::inclusionPath().'/models/'.strtolower($model).'.class.php';
        if (!file_exists($file)) throw new Exception('Model not found : '.$model);
        require_once($file);
    }
    
    protected function requireHelper($helper)
    {
        $file = Context::inclusionPath()."/helpers/{$helper}.lib.php";
        if (!file_exists($file)) throw new Exception('Helper not found : '.$helper);
        require_once($file);
    }
}

?>
