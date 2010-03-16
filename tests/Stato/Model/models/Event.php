<?php

use Stato\Model;

class EventMetadata extends Model\Metadata
{
    public function __construct()
    {
        $this->addProperty('title');
        $this->addProperty('description');
        $this->addProperty('startAt', self::DATETIME);
    }
}

class Event extends Model\Base
{
    public function getDescription()
    {
        return 'hello world';
    }
}

Event::setMetadata(new EventMetadata);

class EventWithoutMeta extends Model\Base {}