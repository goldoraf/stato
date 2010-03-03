<?php

class User
{
    public $id;
    public $fullname;
    public $login;
    public $password;
    
    public function __toString()
    {
        return "<User({$this->fullname}, {$this->login})>";
    }
}

