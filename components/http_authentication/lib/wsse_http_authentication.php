<?php

if (!function_exists('http_parse_headers'))
    throw new Exception('HTTP extension is required');

/**
 * WSSE Authentication
 *
 * See : http://www.xml.com/pub/a/2003/12/17/dive.html
 *
 * NB : the $login_callback method will receive the login as an argument. It must
 * return false if the user is not found or an array containing the user and his password.
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
 *      $this->user =  SWsseHttpAuthentication::authenticate_or_request(
 *          $this->request, $this->response, array('User', 'authenticate'), 'My app'
 *      );
 *      if (!$this->user) return false;
 *  }
 * }
 */
class SWsseHttpAuthentication
{
    private static $header_params = array('Username', 'PasswordDigest', 'Created', 'Nonce');
    
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
        try {
            $params = self::decode_params($request);
            $result = call_user_func($login_callback, $params['Username']);
            if ($result === false) return false;
            list($user, $pwd) = $result;
            $digest = base64_encode(sha1($params['Nonce'].$params['Created'].$pwd, true));
            if ($digest == $params['PasswordDigest'])
                return $user;
            else
                return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public static function authentication_request($response, $realm)
    {
        $response->status = 401;
        $response->headers["WWW-Authenticate"] = 'WSSE realm="'.$realm.'", profile="UsernameToken"';
        $response->body = "HTTP WSSE: Access denied.\n";
        return false;
    }
    
    private static function decode_params($request)
    {
        $params = array();
        $result = http_parse_params(str_replace('UsernameToken ', '', $_SERVER['HTTP_X_WSSE']),
                                    HTTP_PARAMS_ALLOW_COMMA);
        foreach (self::$header_params as $k => $param)
        {
            if (!isset($result->params[$k]) || !isset($result->params[$k][$param]))
                throw new Exception('Unable to parse X-Wsse header');
            
            $params[$param] = $result->params[$k][$param];
        }
        return $params;
    }
}

?>
