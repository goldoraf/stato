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
	private static $exceptions_404 = array(
        'SHttp404', 'SRoutingException', 'SUnknownControllerException',
        'SUnknownActionException'
    );
    
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
            $response = $this->rescue($e);
        }
        
        $response->out();
	}
    
    public function rescue($exception)
    {
        $this->log_error($exception);
        //if (self::$consider_all_requests_local)
            return $this->rescue_locally($exception);
        //else
            //return $this->rescue_in_public($exception);
    }
    
    private function rescue_in_public($exception)
    {
        /*if (($class = self::$exception_notifier) !== null)
        {
            $notifier = new $class();
            $notifier->notify($exception, $this->request, $this->session,
                              self::controller_class($this->request->params['controller']),
                              $this->action_name());
        }*/
        
        list($status, $html) = $this->params_for_rescue($exception);
        return $this->response(file_get_contents(STATO_APP_ROOT_PATH."/public/$html"), $status);
    }
    
    private function rescue_locally($exception)
    {
        list($status, ) = $this->params_for_rescue($exception);
        $template_path = STATO_CORE_PATH.'/gemini/lib/templates/rescue.php';
        $view = new SActionView();
        return $this->response($view->render($template_path, array('exception' => $exception)), $status);
    }
    
    private function params_for_rescue($exception)
    {
        if (in_array(get_class($exception), self::$exceptions_404))
            return array('404 Page Not Found', '404.html');
        else
            return array('500 Internal Error', '500.html');
    }
    
    private function response($body, $status)
    {
        $response = new SResponse();
        $response->headers['Status'] = $status;
        $response->headers['Content-Type'] = 'text/html; charset=utf-8';
        $response->body = $body;
        return $response;
    }
    
    private function log_error($exception)
    {
        SLogger::get_instance()->fatal(get_class($exception)." (".$exception->getMessage().")\n    "
        .implode("\n    ", $this->clean_backtrace($exception))."\n");
    }
    
    private function clean_backtrace($exception)
    {
        foreach ($exception->getTrace() as $t)
        {
            $str = '';
            if (isset($t['file']) && isset($t['line'])) $str.= $t['file'].':'.$t['line'];
            else $str.= 'undefined';
            if (isset($t['class'])) $str.= ' in \''.$t['class'].$t['type'].$t['function'].'\'';
            else $str.= ' in \''.$t['function'].'\'';
            $trace [] = $str;
        }
        return $trace;
    }
}

?>
