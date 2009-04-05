<?php

function stato_error_handler($error_type, $message)
{
    if ($error_type == E_USER_ERROR && preg_match('/^Missing ([a-zA-Z0-9_]*) model$/', $message))
        throw new SDependencyNotFound($message, $error_type);
    
    // No exception thrown for notices : Stato uses some PHP4 librairies
    // and we don't want to bother with "var is deprecated"
    // Ideally, we could log this type of errors
    if ($error_type != E_NOTICE && $error_type != E_STRICT)
        throw new Exception($message, $error_type);
}

class SRescue
{
    private static $default_rescue_status = array
    (
        'SHttp404' => 404, 
        'SRoutingException' => 404, 
        'SUnknownControllerException' => 404,
        'SUnknownActionException' => 404,
        'SRecordNotFound' => 404,
        'SHttpMethodNotImplemented' => 405
    );
    
    public static function response($request, $response, $exception)
    {
        self::notify($request, $exception);
        
        // Let the exception alter the response if it wants.
        // For example, SHttpMethodNotAllowed sets the Allow header.
        if (method_exists($exception, 'handle_response'))
            $response = $exception->handle_response($response);
        
        $status = self::status_for_rescue($exception);
        
        if ($request->format() == 'html')
            $body = self::html_body_response($request, $status, $exception);
        else
            $body = SAbstractSerializer::serialize_exception($request->format(), $exception);
        
        $response->status = $status;
        $response->headers['Content-Type']
            = (string) SMimeType::lookup($request->format()); // et l'encoding UTF-8 ?
        $response->body = $body;
        return $response;
    }
    
    public static function html_body_response($request, $status, $exception)
    {
        if (!SActionController::$consider_all_requests_local)
            return self::html_body_in_public($request, $status, $exception);
        
        return self::html_body_locally($request, $status, $exception);
    }
    
    public static function html_body_in_public($request, $status, $exception)
    {
        $body = '';
        $path = STATO_APP_ROOT_PATH."/public/{$status}.html";
        if (file_exists($path))
            $body = file_get_contents($path);
        
        return $body;
    }
    
    public static function html_body_locally($request, $status, $exception)
    {
        $template_path = STATO_CORE_PATH.'/webflow/lib/templates/rescue.php';
        $view = new SActionView();
        return $view->render($template_path, array('exception' => $exception));
    }
    
    public static function notify($request, $exception)
    {
        /*if (($class = self::$exception_notifier) !== null)
        {
            $notifier = new $class();
            $notifier->notify($exception, $this->request, $this->session,
                              self::controller_class($this->request->params['controller']),
                              $this->action_name());
        }*/
    }
    
    private static function status_for_rescue($exception)
    {
        if (array_key_exists(get_class($exception), self::$default_rescue_status))
            return self::$default_rescue_status[get_class($exception)];
        else
            return 500;
    }
}

?>
