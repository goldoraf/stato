<?php

define('STATO_APP_PATH', STATO_APP_ROOT_PATH.'/app');

function error_handler($error_type, $message)
{
    if ($error_type == E_USER_ERROR && preg_match('/^Missing ([a-zA-Z0-9_]*) model$/', $message))
    {
        SActionController::process_with_exception(new SRequest(), new SResponse(), new Exception($message, $error_type))->out();
        die();
    }
    // No exception thrown for notices : Stato uses some PHP4 librairies
    // and we don't want to bother with "var is deprecated"
    // Ideally, we could log this type of errors
    if ($error_type != E_NOTICE && $error_type != E_STRICT)
        throw new Exception($message, $error_type);
}
set_error_handler('error_handler');

class SDispatchException extends Exception {}

class SDispatcher
{	
	public function dispatch() 
    {
    	try
    	{
            $map = include(STATO_APP_ROOT_PATH.'/conf/routes.php');
            
            SRoutes::initialize($map);
            
            $request  = new SRequest();
            $response = new SResponse();
            
    		if (file_exists($path = STATO_APP_PATH.'/controllers/application_controller.php')) require_once($path);
    		if (file_exists($path = STATO_APP_PATH.'/helpers/application_helper.php')) require_once($path);
    		
    		SActionController::factory(SRoutes::recognize($request), $response)->out();
        }
        catch (Exception $e)
        {
            SActionController::process_with_exception($request, $response, $e)->out();
        }
	}
}

?>
