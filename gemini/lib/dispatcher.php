<?php

define('STATO_APP_PATH', STATO_APP_ROOT_PATH.'/app');

interface SIDispatchable
{
    public static function instanciate($name);
    
    public function dispatch(SRequest $request);
}

class SDispatchException extends Exception {}

class SDispatcher
{	
	public function dispatch() 
    {
    	try
    	{
            $map = include(STATO_APP_ROOT_PATH.'/conf/routes.php');
            SRoutes::initialize($map);
            $request = SRoutes::recognize(new SRequest());
            
    		if (file_exists($path = STATO_APP_PATH.'/helpers/application_helper.php')) require_once($path);
            
            if (isset($request->params['resource']) && !isset($request->params['controller']))
                $dispatchable = SResource::instanciate($request->params['resource']);
            elseif (isset($request->params['controller']) && !isset($request->params['resource']))
                $dispatchable = SActionController::instanciate($request->params['controller']);
            else
                throw new SDispatchException('No dispatchable class identified !');
    		
    		$response = $dispatchable->dispatch($request);
        }
        catch (Exception $e)
        {
            if (SActionController::$consider_all_requests_local)
                $response = SRescue::locally($e);
            else
                $response = SRescue::in_public($e);
        }
        
        $response->out();
	}
}

?>
