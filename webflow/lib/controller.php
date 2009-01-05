<?php

class Stato_ActionNotFound extends Exception {}
class Stato_DoubleRenderError extends Exception {}
class Stato_MissingTemplate extends Exception {}

class Stato_Controller
{
    const TEXT = 'text';
    
    const FILE = 'file';
    
    const TEMPLATE = 'template';
    
    const DEFAULT_RENDER_STATUS_CODE = 200;
    
    protected $assigns = array();
    
    protected $request;
    
    protected $response;
    
    protected $layout;
    
    private $performedRedirect = false;
    
    private $performedRender = false;
    
    private $viewPaths = array();
    
    public function __construct(Stato_Request $request, Stato_Response $response)
    {
        $this->request = $request;
        $this->response = $response;
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
        
        return $this->response;
    }
    
    public function addViewPath($path)
    {
        $this->viewPaths[] = $path;
    }
    
    protected function render($type = null, $content = null, $options = array())
    {
        if ($this->isPerformed())
            throw new Stato_DoubleRenderError('Can only render or redirect once per action');
            
        $defaultOptions = array('status' => null, 'locals' => array(), 'layout' => false);
        $options = array_merge($defaultOptions, $options);
        
        switch ($type) {
            case self::TEXT:
                $this->renderText($content, $options['status'], $options['layout']);
                break;
            case self::FILE:
                $this->renderFile($content, $options['status'], $options['locals'], $options['layout']);
                break;
            case self::TEMPLATE:
                $this->renderTemplate($content, $options['status'], $options['locals'], $options['layout']);
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
    
    private function renderText($text, $status = null, $layout = false)
    {
        if ($layout) {
            $this->contentForLayout = $text;
            $text = $this->renderFile($this->layoutPath());
            $this->eraseRenderResults();
        }
        $this->performedRender = true;
        $this->response->setStatus(!empty($status) ? $status : self::DEFAULT_RENDER_STATUS_CODE);
        $this->response->setBody($text);
        return $text;
    }
    
    private function renderFile($templatePath, $status = null, $locals = array(), $layout = false)
    {
        if (!is_readable($templatePath))
            throw new Stato_MissingTemplate($templatePath);
        
        extract($locals);
        ob_start();
        include ($templatePath);
        $text = ob_get_clean();
        return $this->renderText($text, $status, $layout);
    }
    
    private function renderTemplate($template, $status = null, $locals = array(), $layout = false)
    {
        return $this->renderFile($this->templatePath($template), $status, $locals, $layout);
    }
    
    private function templatePath($template)
    {
        foreach ($this->viewPaths as $path) {
            $possiblePath = "{$path}/{$template}.php";
            if (file_exists($possiblePath)) return $possiblePath;
        }
        throw new Stato_MissingTemplate($template);
    }
    
    private function layoutPath()
    {
        return $this->templatePath("layouts/{$this->layout}");
    }
}
