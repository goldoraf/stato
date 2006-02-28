<?php

class SResponse
{
    public $body    = null;
    public $headers = array();
    
    private static $defaultHeaders = array('Cache-Control' => 'no_cache');
    
    public function __construct()
    {
        $this->headers = array_merge($this->headers, self::$defaultHeaders);
    }
    
    public function redirect($url, $permanently = false)
    {
        if ($this->headers['Status'] != '301 Moved Permanently')
            $this->headers['Status'] = '302 Found';
            
        $this->headers['location'] = $url;
        $this->body = "<html><body>You are being <a href=\"{$url}\">redirected</a>.</body></html>";
    }
    
    public function out()
	{
        foreach($this->headers as $key => $value) header($key.': '.$value);
        echo $this->body;
    }
}

?>
