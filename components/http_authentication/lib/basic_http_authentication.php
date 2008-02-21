<?php

class SBasicHttpAuthentication
{
    public static function authenticate_or_request($request, $response, $login_callback, $realm = 'Application')
    {
        return self::authenticate($request, $response, $login_callback, $realm)
            || self::authentication_request($response, $realm);
    }
    
    public static function authenticate($request, $response, $login_callback, $realm = 'Application')
    {
        list($login, $pwd) = self::login_and_password($request);
        if (!$login || !$pwd) return false;
        return call_user_func_array($login_callback, array($login, $pwd));
    }
    
    public static function authentication_request($response, $realm)
    {
        $response->status = 401;
        $response->headers["WWW-Authenticate"] = 'Basic realm="'.$realm.'"';
        $response->body = "HTTP Basic: Access denied.\n";
        return false;
    }
    
    private static function login_and_password($request)
    {
        $login = (isset($_SERVER['PHP_AUTH_USER'])) ? $_SERVER['PHP_AUTH_USER'] : false;
        $pwd   = (isset($_SERVER['PHP_AUTH_PW'])) ? $_SERVER['PHP_AUTH_PW'] : false;
        return array($login, $pwd);
    }
}

?>
