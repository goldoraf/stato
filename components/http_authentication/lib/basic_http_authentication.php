<?php

/**
 * Basic HTTP Authentication
 *
 * NB : the $login_callback method will receive the login and the password provided as arguments.
 * It must return false if the user is not found or the user object.
 *
 * Example :
 * class ExampleController extends ApplicationController
 * {
 *  public function __construct()
 *  {
 *      $this->add_before_filter('authenticate');
 *  }
 *
 *  public function index()
 *  {
 *      $this->render_text('Hello world with authentication !');
 *  }
 *
 *  private function authenticate()
 *  {
 *      $this->user =  SBasicHttpAuthentication::authenticate_or_request(
 *          $this->request, $this->response, array('User', 'authenticate'), 'My app'
 *      );
 *      if (!$this->user) return false;
 *  }
 * }
 */
class SBasicHttpAuthentication
{
    public static function authenticate_or_request($request, $response, $login_callback, $realm = 'Application')
    {
        $user = self::authenticate($request, $response, $login_callback, $realm);
        if ($user === false)
            return self::authentication_request($response, $realm);
        else
            return $user;
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