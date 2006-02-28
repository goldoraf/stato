<?php

define('APP_DIR', ROOT_DIR.'/app');

function error_handler($errorType, $message)
{
    // No exception thrown for notices : Stato uses some PHP4 librairies
    // and we don't want to bother with "var is deprecated"
    // Ideally, we could log this type of errors
    if ($errorType != E_NOTICE && $errorType != E_STRICT)
        throw new SException($message, $errorType);
}
set_error_handler('error_handler');

class SException extends Exception {}

class SDispatchException extends SException {}

class SDispatcher
{	
	public function dispatch() 
    {
    	try
    	{
            require_once(ROOT_DIR.'/conf/routes.php');
    		
    		SLocale::initialize();
            
            $request  = new SRequest();
            $response = new SResponse();
            
    		if (file_exists($path = APP_DIR.'/controllers/applicationcontroller.class.php')) require_once($path);
    		
    		$controller = SRoutes::recognize($request);
    		
    		$controller->process($request, $response)->out();
        
        }
        catch (Exception $e)
        {
            print_r($e);
        }
	}
}

?>
