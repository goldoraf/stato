<?php

class User
{
    public $id;
    public $fullname;
    public $login;
    public $password;
    
    public function __construct($fullname = null, $login = null, $password = null)
    {
        $this->fullname = $fullname;
        $this->login = $login;
        $this->password = $password;
    }
    
    public function __toString()
    {
        return "<User({$this->fullname}, {$this->login})>";
    }
}

