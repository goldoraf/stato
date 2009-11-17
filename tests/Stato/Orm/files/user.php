<?php

class User
{
    public $fullname;
    public $login;
    public $password;
    
    public function __toString()
    {
        return "<User({$this->fullname}, {$this->login})>";
    }
}

