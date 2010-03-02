<?php

namespace Stato\Webflow;

class DispatchException extends \Exception {}
class ControllerNotFound extends \Exception {}

class Dispatcher
{
    protected $request;
    protected $response;
    protected $routeset;
    public $plugins;
 
    protected $controllerDirs = array();
    
    public function __construct(RouteSet $routeset)
    {
        $this->routeset = $routeset;
	$this->plugins = new Plugin\Broker();
    }
    
    public function addControllerDir($dir)
    {
        $this->controllerDirs[] = $dir;
    }
    
    public function dispatch(Request $request = null, Response $response = null)
    {
        $this->request  = ($request  !== null) ? $request  : new Request();
        $this->response = ($response !== null) ? $response : new Response();

       	$this->plugins->setRequest($this->request)->setResponse($this->response);
 
        $this->plugins->preRouting($this->request);

        $pathInfo = $this->request->getPathInfo();
        $params = $this->routeset->recognizePath($pathInfo);
        $this->request->setParams($params);

 
        $controllerName = $this->request->getParam('controller');
        if (empty($controllerName))
            throw new DispatchException('No controller specified in this request');
        
	$this->plugins->postRouting();       

	$this->plugins->preDispatch();       
        $controllerClass = $this->getControllerClass($controllerName);
        $controllerPath = $this->getControllerPath($controllerName);
        
        require_once $controllerPath;
        if (!class_exists($controllerClass, false))
            throw new ControllerNotFound("$controllerClass class not found");
            
        $controller = new $controllerClass($this->request, $this->response);
        $controller->addViewDir(dirname($controllerPath).'/../views');
        $controller->run();

	$this->plugins->postDispatch();       
        
        $this->response->send();
    }
    
    protected function getControllerClass($controllerName)
    {
        return preg_replace('/(^|_)(.)/e', "strtoupper('\\2')", $controllerName).'Controller';
    }
    
    protected function getControllerPath($controllerName)
    {
	$controllerName = ucfirst(strtolower($controllerName));
        foreach ($this->controllerDirs as $dir) {
            $possiblePath = "{$dir}/{$controllerName}Controller.php";
            if (file_exists($possiblePath)) return $possiblePath;
        }
        throw new ControllerNotFound("$controllerName controller file not found");
    }
}
