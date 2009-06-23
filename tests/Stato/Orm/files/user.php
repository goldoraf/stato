<?php

use Stato\Orm\Table;
use Stato\Orm\Column;

class User
{
    public static $table;
    
    /*public $fullname;
    public $login;
    public $password;*/
    
    public function __toString()
    {
        return "<User({$this->fullname}, {$this->login})>";
    }
}

$users = new Table('users', array(
    new Column('id', Column::INTEGER, array('primary_key' => true)),
    new Column('fullname', Column::STRING),
    new Column('login', Column::STRING),
    new Column('password', Column::STRING),
));

User::$table = $users;