<?php

function error_handler($errorType, $message)
{
    // No exception thrown for notices : Stato uses some PHP4 librairies
    // and we don't want to bother with "var is deprecated"
    // Ideally, we could log this type of errors
    if ($errorType != E_NOTICE && $errorType != E_STRICT)
        throw new Exception( $message, $errorType);
}
set_error_handler('error_handler');

class DispatchException extends Exception {}

/**
 * Dispatcher
 * 
 * @package 
 * @author goldoraf
 * @copyright Copyright (c) 2005
 * @version 0.1
 * @access public
 **/
class Dispatcher
{	
	/**
	 * Dispatcher::dispatch()
	 * 
	 * @return void
	 **/
	public function dispatch() 
    {
		Context::init();
        $request = Context::$request;
        
        $moduleName = preg_replace('/[^a-z0-9\-_]+/i', '', $request->module);
		$controName = preg_replace('/[^a-z0-9\-_]+/i', '', $request->controller);
        $actionName = preg_replace('/[^a-z0-9\-_]+/i', '', $request->action);
        
        try
        {
            if (empty($moduleName)) $moduleName = 'root';
            
            if ($moduleName != 'root' && !is_dir(APP_DIR.'/modules/'.$moduleName))
            {
                throw new DispatchException($moduleName.' module not found !');
            }
            if (empty($controName))
            {
                throw new DispatchException('No controller specified in this request !');
            }
            // tentative d'inclusion de l'ApplicationController
    		if (file_exists($path = $this->getControllerPath('root', 'application')))
    		{
    			require_once($path);
    		}
            // instanciation du controller
    		if (!file_exists($path = $this->getControllerPath($moduleName, $controName)))
    		{
    			throw new DispatchException(ucfirst($controName).'Controller not found !');
    		}
    		require_once($path);
    		$controllerName = $controName.'controller';
    		$controller = new $controllerName();
    		
    		if (empty($actionName))
    		{
                $actionName = 'index';
                $request->action = 'index';
            }
    		
    		// execution de l'action
    		if (!$controller->actionExists($actionName))
    		{
                throw new DispatchException($actionName.' action is not defined in '.ucfirst($controName).'Controller!');
            }
            $controller->callAction($actionName);
            
            // rendu du template
            $controller->render();
        }
        catch (DispatchException $e)
        {
            if (APP_MODE == 'prod')
            {
                header('location:'.BASE_DIR.'/public/html/404.html');
                exit();
            }
            else
            {
                $c = new ActionController();
                $c->renderText("<html><body><strong>Dispatch error : </strong>".$e->getMessage()."</body></html>");
            }
        }
        catch (Exception $e)
        {
            if (APP_MODE == 'prod')
            {
                header('location:'.BASE_DIR.'/public/html/500.html');
                exit();
            }
            else
            {
                $c = new ActionController();
                $c->exception  = $e;
                $c->controller = ucfirst($controName).'Controller';
                $c->action     = $actionName;
                $c->renderFile(ROOT_DIR.'/core/view/templates/rescue/exception.php');
            }
        }
	}
	
	private function getControllerPath($module, $controller)
	{
        if ($module == 'root') return APP_DIR.'/controllers/'.$controller.'controller.class.php';
        return APP_DIR.'/modules/'.$module.'/controllers/'.$controller.'controller.class.php';
    }
	
}

?>
