<?php

class SPhpSession implements ArrayAccess
{
    private $data = array();
    private $cookie_name = 'stck';
    private $cookie_path = '/';
    private $cookie_domain = null;
    private $cookie_secure = false;
    private $handler;
    
    public function __construct()
    {
        if (function_exists('ini_get'))
		{
			$this->cookie_path   = ini_get('session.cookie_path');
			$this->cookie_domain = ini_get('session.cookie_domain');
			$this->cookie_secure = ini_get('session.cookie_secure');
		}
        
        if (SActionController::$session_handler != 'default') $this->set_handler();
    }
    
    public function __destruct()
	{
		if (isset($_SESSION)) session_write_close();
	}
	
	public function start()
	{
        if (!isset($_COOKIE[$this->cookie_name]))
			session_id(sha1(uniqid(rand(),true)));
        
        session_name($this->cookie_name);
        session_start();
        $this->data = $_SESSION;
    }
    
    public function store()
    {
        $_SESSION = $this->data;
    }
    
    public function destroy()
    {
        $_SESSION = array();
        session_unset();
		session_destroy();
		// Firefox only remove the cookie when you submit the same values for all parameters, 
        // except the date, which sould be in the past
        setcookie(session_name(), false, time() -600, $this->cookie_path, $this->cookie_domain, $this->cookie_secure);
    }
    
    public function id()
    {
        return session_id();
    }
    
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }
    
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) return $this->data[$offset];
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) unset($this->data[$offset]);
        return;
    }
    
    private function set_handler()
    {
        $handler_class = 'S'.SActionController::$session_handler.'SessionHandler';
        $this->handler = new $handler_class();
        
        session_set_save_handler(
			array(&$this->handler, 'open'),
			array(&$this->handler, 'close'),
			array(&$this->handler, 'read'),
			array(&$this->handler, 'write'),
			array(&$this->handler, 'destroy'),
			array(&$this->handler, 'gc')
		);
    }
}

interface SPhpSessionHandler
{
    public function open();
    public function close();
    public function read($key);
    public function write($key, $data);
    public function destroy($key);
    public function gc($max);
}

?>
