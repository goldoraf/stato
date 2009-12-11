<?php

use Stato\Orm\Entity;

class User extends Entity
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

