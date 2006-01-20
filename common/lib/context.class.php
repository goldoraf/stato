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
        
        Routes::initialize();
        
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
}

?>
