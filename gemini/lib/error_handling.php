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

?>