<?php



class Stato_Webflow_DispatchException extends Exception {}
class Stato_Webflow_ControllerNotFound extends Exception {}

class Stato_Webflow_Dispatcher
{
    protected $request;
    protected $response;
    protected $routeset;
    public $plugins;
 
    protected $controllerDirs = array();
    
    public function __construct(Stato_Webflow_RouteSet $routeset)
    {
        $this->routeset = $routeset;
	$this->plugins = new Stato_Webflow_Plugin_Broker();
    }
    
    public function addControllerDir($dir)
    {
        $this->controllerDirs[] = $dir;
    }
    
    public function dispatch(Stato_Webflow_Request $request = null, Stato_Webflow_Response $response = null)
    {
        $this->request  = ($request  !== null) ? $request  : new Stato_Webflow_Request();
        $this->response = ($response !== null) ? $response : new Stato_Webflow_Response();

       	$this->plugins->setRequest($this->request)->setResponse($this->response);
 
        $this->plugins->preRouting($this->request);

        $pathInfo = $this->request->getPathInfo();
        $params = $this->routeset->recognizePath($pathInfo);
        $this->request->setParams($params);

 
        $controllerName = $this->request->getParam('controller');
        if (empty($controllerName))
            throw new Stato_Webflow_DispatchException('No controller specified in this request');
        
	$this->plugins->postRouting();       

	$this->plugins->preDispatch();       
        $controllerClass = $this->getControllerClass($controllerName);
        $controllerPath = $this->getControllerPath($controllerName);
        
        require_once $controllerPath;
        if (!class_exists($controllerClass, false))
            throw new Stato_Webflow_ControllerNotFound("$controllerClass class not found");
            
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
        throw new Stato_Webflow_ControllerNotFound("$controllerName controller file not found");
    }
}
