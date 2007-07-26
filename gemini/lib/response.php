<?php

class SResponse
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
}

?>
