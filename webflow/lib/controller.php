<?php

class Stato_ActionNotFound extends Exception {}
class Stato_DoubleRenderError extends Exception {}

class Stato_Controller
{
    const TEXT = 'text';
    
    const TEMPLATE = 'template';
    
    const ACTION = 'action';
    
    const DEFAULT_RENDER_STATUS_CODE = 200;
    
    protected $assigns = array();
    
    protected $request;
    
    protected $response;
    
    protected $view;
    
    protected $layout;
    
    private $performedRedirect = false;
    
    private $performedRender = false;
    
    public function __construct(Stato_Request $request, Stato_Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = new Stato_View();
    }
    
    public function __get($name)
    {
        return $this->__isset($name) ? $this->assigns[$name] : null;
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
    
    public function run()
    {
        $action = $this->getActionName();
        if (!$this->actionExists($action))
            throw new Stato_ActionNotFound($action);
            
        $this->$action();
        
        if (!$this->isPerformed()) $this->render();
        
        return $this->response;
    }
    
    public function addViewPath($path)
    {
        $this->view->addPath($path);
    }
    
    protected function render($type = null, $content = null, $options = array())
    {
        if ($this->isPerformed())
            throw new Stato_DoubleRenderError('Can only render or redirect once per action');
            
        $defaultOptions = array('status' => null, 'locals' => array(), 'layout' => false);
        $options = array_merge($defaultOptions, $options);
        
        switch ($type) {
            case null:
                $this->renderTemplate($this->defaultTemplateName(), $options);
                break;
            case self::ACTION:
                $this->renderTemplate($this->defaultTemplateName($content), $options);
                break;
            case self::TEMPLATE:
                $this->renderTemplate($content, $options);
                break;
            case self::TEXT:
                $this->renderText($content, $options['status']);
                break;
        }
    }
    
    protected function eraseRenderResults()
    {
        $this->response->setBody('');
        $this->performedRender = false;
    }
    
    protected function isPerformed()
    {
        return $this->performedRender || $this->performedRedirect;
    }
    
    protected function getControllerName()
    {
        return underscore(str_replace('Controller', '', get_class($this)));
    }
    
    protected function getActionName()
    {
        $action = $this->request->getParam('action');
        if (empty($action)) $action = 'index';
        return $action;
    }
    
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
    
    private function renderTemplate($template, $options = array())
    {
        if ($options['layout'] === true) 
            $options['layout'] = $this->layout;
            
        $this->view->assign($this->assigns);
        $text = $this->view->render($template, $options);
        return $this->renderText($text, $options['status']);
    }
    
    private function renderText($text, $status = null)
    {
        $this->performedRender = true;
        $this->response->setStatus(!empty($status) ? $status : self::DEFAULT_RENDER_STATUS_CODE);
        $this->response->setBody($text);
        return $text;
    }
    
    private function defaultTemplateName($actionName = null)
    {
        if ($actionName === null) $actionName = $this->getActionName();
        return $this->getControllerName().'/'.$actionName;
    }
}
