<?php

namespace Stato\Webflow;

class Session implements \ArrayAccess
{
    private $handler;
    private $cookieName = 'stck';
    private $cookiePath = '/';
    private $cookieDomain = null;
    private $cookieSecure = false;
    
    public function __construct(ISessionHandler $handler = null)
    {
        if (function_exists('ini_get')) {
            $this->cookiePath   = ini_get('session.cookie_path');
            $this->cookieDomain = ini_get('session.cookie_domain');
            $this->cookieSecure = ini_get('session.cookie_secure');
        }
        
        $this->setHandler($handler);
    }
    
    public function start()
    {
        if (!isset($_COOKIE[$this->cookieName])) session_id(sha1(uniqid(rand(),true)));
        
        session_name($this->cookieName);
        session_start();
    }
    
    public function store()
    {
        if (isset($_SESSION)) session_write_close();
    }
    
    public function destroy()
    {
        session_destroy();
        // Firefox only remove the cookie when you submit the same values for all parameters, 
        // except the date, which should be in the past
        setcookie(session_name(), false, time() -600, $this->cookiePath, $this->cookieDomain, $this->cookieSecure);
    }
    
    public function id()
    {
        return session_id();
    }
    
    public function offsetExists($offset)
    {
        return isset($_SESSION['__STATO__'][$offset]);
    }
    
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) return $_SESSION['__STATO__'][$offset];
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        if (is_array($value)) $value = new ArrayObject($value);
        $_SESSION['__STATO__'][$offset] = $value;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) unset($_SESSION['__STATO__'][$offset]);
        return;
    }
    
    private function setHandler(ISessionHandler $handler = null)
    {
        if ($handler === null) return;
        
        $this->handler = $handler;
        
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

interface ISessionHandler
{
    public function open();
    public function close();
    public function read($key);
    public function write($key, $data);
    public function destroy($key);
    public function gc($max);
}
