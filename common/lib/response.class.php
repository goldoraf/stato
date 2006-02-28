<?php

class SResponse implements ArrayAccess
{
    public $body    = null;
    public $headers = array();
    public $values  = array();
    
    private static $defaultHeaders = array('Cache-Control' => 'no_cache');
    
    public function __construct()
    {
        $this->headers = array_merge($this->headers, self::$defaultHeaders);
    }
    
    public function offsetExists($offset)
    {
        if (isset($this->values[$offset])) return true;
        return false;
    }
    
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) return $this->values[$offset];
        return false;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = $value;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) unset($this->values[$offset]);
        return;
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
