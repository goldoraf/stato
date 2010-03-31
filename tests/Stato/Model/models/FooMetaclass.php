<?php

use Stato\Model;

class FooMetaclass extends Model\Metaclass
{
    public function __construct()
    {
        $this->setModelClass('Foo');
        $this->addProperty('id',  self::SERIAL);
        $this->addProperty('bar', self::STRING);
    }
}
