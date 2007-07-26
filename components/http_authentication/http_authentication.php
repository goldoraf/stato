<?php

class SHttpAuthentication
{
    public static function authenticate($controller, $error_msg, $authorized_users = array())
    {
        list($login, $pwd) = self::get_auth_data();
        if (!$login || !self::authenticate_user($login, $pwd, $authorized_users))
            return self::send_response($controller, $error_msg);
        return true;
    }
    
    private static function send_response($controller, $error_msg)
    {
        $controller->response->headers["Status"] = "Unauthorized";
        $controller->response->headers["WWW-Authenticate"] = "Basic";
        $controller->render_text($error_msg, 401);
        return false;
    }
    
    private static function authenticate_user($provided_login, $provided_pwd, $authorized_users)
    {
        foreach ($authorized_users as $login => $pwd)
			if ($login == $provided_login && $pwd == $provided_pwd) return true;
		return false;
    }
    
    private static function get_auth_data()
    {
        $login = (isset($_SERVER['PHP_AUTH_USER'])) ? $_SERVER['PHP_AUTH_USER'] : false;
        $pwd   = (isset($_SERVER['PHP_AUTH_PW'])) ? $_SERVER['PHP_AUTH_PW'] : false;
        
        return array($login, $pwd);
    }
}

?>
