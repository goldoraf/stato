<?php

function error_handler($error_type, $message)
{
    if ($error_type == E_USER_ERROR && preg_match('/^Missing ([a-zA-Z0-9_]*) model$/', $message))
    {
        $exception = new SDependencyNotFound($message, $error_type);
        
        if (defined('STDIN') && defined('STDOUT') && defined('STDERR')) // CLI environment
            throw $exception;
        else
        {
            $dispatcher = new SDispatcher();
            $dispatcher->rescue($exception)->out();
        }
        
        die();
    }
    // No exception thrown for notices : Stato uses some PHP4 librairies
    // and we don't want to bother with "var is deprecated"
    // Ideally, we could log this type of errors
    if ($error_type != E_NOTICE && $error_type != E_STRICT)
        throw new Exception($message, $error_type);
}
set_error_handler('error_handler');

class SRescue
{
    private static $default_rescue_status = array
    (
        'SHttp404' => 404, 
        'SRoutingException' => 404, 
        'SUnknownControllerException' => 404,
        'SUnknownActionException' => 404,
        'SRecordNotFound' => 404,
    );
    
    public static function in_public($request, $exception)
    {
        /*if (($class = self::$exception_notifier) !== null)
        {
            $notifier = new $class();
            $notifier->notify($exception, $this->request, $this->session,
                              self::controller_class($this->request->params['controller']),
                              $this->action_name());
        }*/
        self::log_error($exception);
        $status = self::status_for_rescue($exception);
        $body = null;
        if ($request->format() == 'html')
        {
            $path = STATO_APP_ROOT_PATH."/public/{$status}.html";
            if (file_exists($path))
                $body = file_get_contents($path);
        }
        return self::response($body, $status);
    }
    
    public static function locally($request, $exception)
    {
        self::log_error($exception);
        $status = self::status_for_rescue($exception);
        if ($request->format() == 'html')
        {
            $template_path = STATO_CORE_PATH.'/gemini/lib/templates/rescue.php';
            $view = new SActionView();
            return self::response($view->render($template_path, array('exception' => $exception)), $status);
        }
    }
    
    private static function status_for_rescue($exception)
    {
        if (array_key_exists(get_class($exception), self::$default_rescue_status))
            return self::$default_rescue_status[get_class($exception)];
        else
            return 500;
    }
    
    private static function response($body, $status)
    {
        $response = new SResponse();
        $response->status = $status;
        $response->headers['Content-Type'] = 'text/html; charset=utf-8';
        $response->body = $body;
        return $response;
    }
    
    private static function log_error($exception)
    {
        SLogger::get_instance()->fatal(get_class($exception)." (".$exception->getMessage().")\n    "
        .implode("\n    ", self::clean_backtrace($exception))."\n");
    }
    
    private static function clean_backtrace($exception)
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
