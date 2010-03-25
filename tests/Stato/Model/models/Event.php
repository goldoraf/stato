<?php

use Stato\Model;

class EventMetaclass extends Model\Metaclass
{
    public function __construct()
    {
        $this->addProperty('id',          self::SERIAL);
        $this->addProperty('title',       self::STRING);
        $this->addProperty('description', self::TEXT);
        $this->addProperty('startAt',     self::DATETIME);
    }
}

class Event extends Model\Base
{
    public function getDescription()
    {
        return 'hello world';
    }
}

Event::setMetaclass(new EventMetaclass);

class EventWithoutMeta extends Model\Base {}