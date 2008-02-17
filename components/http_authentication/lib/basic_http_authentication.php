<?php

class SBasicHttpAuthentication
{
    public static function authenticate($request, $response, $login_callback, $realm = 'Application')
    {
        list($login, $pwd) = self::login_and_password($request);
        if (!$login || !$pwd || !call_user_func_array($login_callback, array($login, $pwd)))
            return self::authentication_request($response, $realm);
        return true;
    }
    
    private static function authentication_request($response, $realm)
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
