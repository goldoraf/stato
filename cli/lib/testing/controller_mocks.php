<?php

class MockRequest
{
    public $controller = Null;
    public $action     = Null;
    public $params     = array();
    public $method     = 'GET';
    public $request_uri = '/';
    public $host       = 'test.host';
    public $port       = 80;
    public $remote_ip   = '0.0.0.0';
    public $ssl        = false;
    
    public $relative_url_root = Null;
    
    public function __call($method, $args)
    {
        return $this->$method;
    }
    
    public function is_post()
    {
        return $this->method() == 'POST';
    }
    
    public function is_get()
    {
        return $this->method() == 'GET';
    }
    
    public function is_ssl()
    {
        return $this->ssl;
    }
    
    public function protocol()
    {
        return ($this->is_ssl() ? 'https://' : 'http://');
    }
    
    public function standard_port()
    {
        return (($this->protocol() == 'https://') ? 443 : 80);
    }
    
    public function port_string()
    {
        return (($this->port == $this->standard_port()) ? '' : ':'.$this->port);
    }
    
    public function host_with_port()
    {
        return $this->host.$this->port_string();
    }
    
    public function relative_url_root()
    {
        if (!isset($this->relative_url_root))
            $this->relative_url_root = str_replace('/index.php', '/', $_SERVER['SCRIPT_NAME']);
        return $this->relative_url_root;
    }
}

class MockResponse
{
    public $body         = null;
    public $assigns      = array();
    public $headers      = array();
    public $redirected_to = null;
    
    private static $default_headers = array('Cache-Control' => 'no_cache');
    
    public function __construct()
    {
        $this->headers = array_merge($this->headers, self::$default_headers);
    }
    
    public function redirect($url, $permanently = false)
    {
        if ($this->headers['Status'] != '301 Moved Permanently')
            $this->headers['Status'] = '302 Found';
            
        $this->headers['location'] = $url;
        $this->body = "<html><body>You are being <a href=\"{$url}\">redirected</a>.</body></html>";
    }
    
    public function send_headers()
    {
        foreach($this->headers as $key => $value) header($key.': '.$value);
    }
    
    public function out()
	{
        $this->send_headers();
        echo $this->body;
    }
    
    public function code()
    {
        return substr($this->headers['Status'], 0, 3);
    }
    
    public function message()
    {
        return substr($this->headers['Status'], 4, strlen($this->headers['Status']));
    }
    
    public function is_success()
    {
        return ($this->code() == 200);
    }
    
    public function is_missing()
    {
        return ($this->code() == 404);
    }
    
    public function is_redirect()
    {
        return ($this->code() >= 300 && $this->code() <= 399);
    }
    
    public function is_error()
    {
        return ($this->code() >= 500 && $this->code() <= 599);
    }
    
    public function redirect_url()
    {
        return ($this->is_redirect() ? $this->headers['location'] : null);
    }
}

class MockSession implements ArrayAccess
{
    private $vars = array();
    
    public function session_id()
    {
        return 'fake_session_id';
    }
    
    public function offsetExists($offset)
    {
        if (isset($this->vars[$offset])) return true;
        return false;
    }
    
    public function offsetGet($offset)
    {
        if ($this->offset_exists($offset)) return $this->vars[$offset];
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->vars[$offset] = $value;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offset_exists($offset)) unset($this->vars[$offset]);
        return;
    }
}

?>
