<?php

class SCookieSession extends SAbstractSession
{
    private $name = 'session_cookie';
    private $expire = 3600;
    
    public function __construct()
    {
        $this->data = unserialize(stripslashes(SEncryption::decrypt($_COOKIE[$this->name])));
    }
    
    public function store()
    {
        setcookie($this->name, SEncryption::encrypt(serialize($this->data)), time() + $this->expire);
    }
    
    public function destroy()
    {
        setcookie($this->name, '', 0);
    }
    
    public function session_id()
    {
        return 'cookie';
    }
}

?>
