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
    public static function in_public($exception)
    {
        /*if (($class = self::$exception_notifier) !== null)
        {
            $notifier = new $class();
            $notifier->notify($exception, $this->request, $this->session,
                              self::controller_class($this->request->params['controller']),
                              $this->action_name());
        }*/
        self::log_error($exception);
        list($status, $html) = self::params_for_rescue($exception);
        return self::response(file_get_contents(STATO_APP_ROOT_PATH."/public/$html"), $status);
    }
    
    public static function locally($exception)
    {
        self::log_error($exception);
        list($status, ) = self::params_for_rescue($exception);
        $template_path = STATO_CORE_PATH.'/gemini/lib/templates/rescue.php';
        $view = new SActionView();
        return self::response($view->render($template_path, array('exception' => $exception)), $status);
    }
    
    private static function params_for_rescue($exception)
    {
        if (in_array(get_class($exception), self::$exceptions_404))
            return array('404 Page Not Found', '404.html');
        else
            return array('500 Internal Error', '500.html');
    }
    
    private static function response($body, $status)
    {
        $response = new SResponse();
        $response->headers['Status'] = $status;
        $response->headers['Content-Type'] = 'text/html; charset=utf-8';
        $response->body = $body;
        return $response;
    }
    
    private static function log_error($exception)
    {
        SLogger::get_instance()->fatal(get_class($exception)." (".$exception->getMessage().")\n    "
        .implode("\n    ", $this->clean_backtrace($exception))."\n");
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
