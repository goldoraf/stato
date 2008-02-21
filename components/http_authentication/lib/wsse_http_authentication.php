<?php

class SWSSEHttpAuthentication
{
    public static function authenticate($request, $response, $auth_callback, $realm = 'Application')
    {
        if (!call_user_func_array($auth_callback, array()))
            return self::authentication_request($response, $realm);
        return true;
    }
    
    private static function authentication_request($response, $realm)
    {
        $response->status = 401;
        $response->headers["WWW-Authenticate"] = 'WSSE realm="'.$realm.'", profile="UsernameToken"';
        $response->body = "HTTP WSSE: Access denied.\n";
        return false;
    }
}

?>
