<?php

use Stato\Model;

class UserMetaclass extends Model\Metaclass
{
    public function __construct()
    {
        $this->addProperty('id',          self::SERIAL);
        $this->addProperty('fullname',    self::STRING);
        $this->addProperty('login',       self::STRING, array('required' => true));
        $this->addProperty('password',    self::STRING, array('required' => true));
        $this->addProperty('country',     self::STRING, array('index' => true));
        $this->addProperty('activated',   self::BOOLEAN, array('default' => false));
        $this->addProperty('registredOn', self::DATETIME, array('column' => 'registration_date'));
    }
}

class User extends Model\Base
{
    public function getDescription()
    {
        return 'hello world';
    }
}

User::setMetaclass(new UserMetaclass);