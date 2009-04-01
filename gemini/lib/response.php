<?php

class SInvalidHttpStatusCode extends Exception {}

class SResponse
{
    public $body         = null;
    public $status       = null;
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
        if ($permanently) $this->status = 301;
        else $this->status = 302;
            
        $this->headers['location'] = $url;
        $this->body = "<html><body>You are being <a href=\"{$url}\">redirected</a>.</body></html>";
    }
    
    public function send_headers()
    {
        if (!is_int($this->status) || ($this->status < 100) || ($this->status > 599))
            throw new SInvalidHttpStatusCode((string) $this->status);
        
        header('HTTP/1.x '.$this->status);
		foreach($this->headers as $key => $value) header($key.': '.$value);
    }
    
    public function out()
	{
        $this->send_headers();
        echo $this->body;
    }
}

?>
