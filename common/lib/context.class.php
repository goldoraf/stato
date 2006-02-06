<?php

/**
* 
* Encapsule les objets partagés (request, response, session)
* 
* 
* @package 
* @version 0.1
* 
*/
class Context
{
    public static $request  = null;
    public static $response = null;
    public static $session  = null;
    
    private static $status = False;
    
    public static function init()
    {
        if (Context::$status) return;
        
        require_once(ROOT_DIR.'/conf/routes.php');
        
        self::$request  = new Request();
        self::$response = new Response();
        self::$session  = new Session();
        
        Locale::initialize();
        
        self::$status = True;
    }
    
    public static function locale($key)
    {
        return Locale::translate($key);
    }
    
    public static function inclusionPath($module = Null)
    {
        if ($module == Null) $module = self::$request->module;
        if ($module == 'root') return APP_DIR;
        return APP_DIR."/modules/$module";
    }
}

?>
