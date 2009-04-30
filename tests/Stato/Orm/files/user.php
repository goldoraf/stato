<?php

use Stato\Orm\Table;
use Stato\Orm\Column;
use Stato\Orm\Mapper;

class User
{
    public static $table;
    
    public $fullname;
    public $login;
    public $password;
    
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

Mapper::addClass('User', $users);
User::$table = $users;