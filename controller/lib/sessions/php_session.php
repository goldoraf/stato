<?php

class SPhpSession extends SAbstractSession
{
    public function __construct()
    {
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
        session_destroy();
    }
    
    public function session_id()
    {
        return session_id();
    }
}

?>
