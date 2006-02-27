<?php

class SDispatchException extends SException {}

class SDispatcher
{	
	public function dispatch() 
    {
		SContext::init();
        $request = SContext::$request;
        
        $moduleName = preg_replace('/[^a-z0-9\-_]+/i', '', $request->module);
		$controName = preg_replace('/[^a-z0-9\-_]+/i', '', $request->controller);
        $actionName = preg_replace('/[^a-z0-9\-_]+/i', '', $request->action);
        
        try
        {
            if (empty($moduleName)) $moduleName = 'root';
            
            if ($moduleName != 'root' && !is_dir(APP_DIR.'/modules/'.$moduleName))
                throw new SDispatchException($moduleName.' module not found !');
            
            if (empty($controName))
                throw new SDispatchException('No controller specified in this request !');
            
            // tentative d'inclusion de l'ApplicationController
    		if (file_exists($path = $this->getControllerPath('root', 'application'))) require_once($path);
    		
            // instanciation du controller
    		if (!file_exists($path = $this->getControllerPath($moduleName, $controName)))
    			throw new SDispatchException(ucfirst($controName).'Controller not found !');
    		
    		require_once($path);
    		$controllerName = $controName.'controller';
    		$controller = new $controllerName();
    		
    		// execution de l'action
    		if (!$controller->actionExists($actionName))
                throw new SDispatchException($actionName.' action is not defined in '.ucfirst($controName).'Controller!');
            
            $controller->performAction($actionName);
        }
        catch (SDispatchException $e)
        {
            $c = new SActionController();
            if (APP_MODE == 'prod')
                $c->renderText(file_get_contents(ROOT_DIR.'/public/404.html'));
            else
                $c->renderText("<html><body><strong>Dispatch error : </strong>".$e->getMessage()."</body></html>");
            exit();
        }
        catch (Exception $e)
        {
            $c = new SActionController();
            if (APP_MODE == 'prod')
                $c->renderText(file_get_contents(ROOT_DIR.'/public/500.html'));
            else
            {
                $c->exception  = $e;
                $c->controller = ucfirst($controName).'Controller';
                $c->action     = $actionName;
                $c->renderFile(ROOT_DIR.'/core/view/templates/rescue/exception.php');
            }
            exit();
        }
	}
	
	private function getControllerPath($module, $controller)
	{
        if ($module == 'root') return APP_DIR.'/controllers/'.$controller.'controller.class.php';
        return APP_DIR.'/modules/'.$module.'/controllers/'.$controller.'controller.class.php';
    }
	
}

?>
