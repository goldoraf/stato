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

class SDispatchException extends SException {}

class SDispatcher
{	
	public function dispatch() 
    {
    	try
    	{
            $map = include(ROOT_DIR.'/conf/routes.php');
            
            SRoutes::initialize($map);
    		
    		SLocale::initialize();
            
            $request  = new SRequest();
            $response = new SResponse();
            
    		if (file_exists($path = APP_DIR.'/controllers/application_controller.php')) require_once($path);
    		if (file_exists($path = APP_DIR.'/helpers/application_helper.php')) require_once($path);
    		
    		SActionController::factory(SRoutes::recognize($request), $response)->out();
        }
        catch (Exception $e)
        {
            SActionController::processWithException($request, $response, $e)->out();
        }
	}
}

?>
