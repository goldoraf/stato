<?php

class MockRequest
{
    public $controller = Null;
    public $action     = Null;
    public $params     = array();
    public $method     = 'GET';
    public $requestUri = '/';
    public $host       = 'test.host';
    public $port       = 80;
    public $remoteIp   = '0.0.0.0';
    public $ssl        = false;
    
    public $relativeUrlRoot = Null;
    
    public function __call($method, $args)
    {
        return $this->$method;
    }
    
    public function isPost()
    {
        return $this->method() == 'POST';
    }
    
    public function isGet()
    {
        return $this->method() == 'GET';
    }
    
    public function isSSL()
    {
        return $this->ssl;
    }
    
    public function protocol()
    {
        return ($this->isSSL() ? 'https://' : 'http://');
    }
    
    public function standardPort()
    {
        return (($this->protocol() == 'https://') ? 443 : 80);
    }
    
    public function portString()
    {
        return (($this->port == $this->standardPort()) ? '' : ':'.$this->port);
    }
    
    public function hostWithPort()
    {
        return $this->host.$this->portString();
    }
    
    public function relativeUrlRoot()
    {
        if (!isset($this->relativeUrlRoot))
            $this->relativeUrlRoot = str_replace('/index.php', '/', $_SERVER['SCRIPT_NAME']);
        return $this->relativeUrlRoot;
    }
}

class MockResponse extends SResponse
{
    public function code()
    {
        return substr($this->headers['Status'], 0, 3);
    }
    
    public function message()
    {
        return substr($this->headers['Status'], 4, strlen($this->headers['Status']));
    }
    
    public function isSuccess()
    {
        return ($this->code() == 200);
    }
    
    public function isMissing()
    {
        return ($this->code() == 404);
    }
    
    public function isRedirect()
    {
        return ($this->code() >= 300 && $this->code() <= 399);
    }
    
    public function isError()
    {
        return ($this->code() >= 500 && $this->code() <= 599);
    }
    
    public function redirectUrl()
    {
        return ($this->isRedirect() ? $this->headers['location'] : null);
    }
}

class MockSession implements ArrayAccess
{
    private $vars = array();
    
    public function sessionId()
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
        if ($this->offsetExists($offset)) return $this->vars[$offset];
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->vars[$offset] = $value;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) unset($this->vars[$offset]);
        return;
    }
}

?>
