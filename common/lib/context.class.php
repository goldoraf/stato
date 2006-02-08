<?php

function error_handler($errorType, $message)
{
    // No exception thrown for notices : Stato uses some PHP4 librairies
    // and we don't want to bother with "var is deprecated"
    // Ideally, we could log this type of errors
    if ($errorType != E_NOTICE && $errorType != E_STRICT)
        throw new SException( $message, $errorType);
}
set_error_handler('error_handler');

class SException extends Exception {}

class SContext
{
    public static $request  = null;
    public static $response = null;
    public static $session  = null;
    
    private static $status = False;
    
    public static function init()
    {
        if (SContext::$status) return;
        
        require_once(ROOT_DIR.'/conf/routes.php');
        
        self::$request  = new SRequest();
        self::$response = new SResponse();
        self::$session  = new SSession();
        
        SLocale::initialize();
        
        self::$status = True;
    }
    
    public static function locale($key)
    {
        return SLocale::translate($key);
    }
    
    public static function inclusionPath($module = Null)
    {
        if ($module == Null) $module = self::$request->module;
        if ($module == 'root') return APP_DIR;
        return APP_DIR."/modules/$module";
    }
}

?>
