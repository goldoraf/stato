<?php

use Stato\Model;

class ContactMetaclass extends Model\Metaclass
{
    public function __construct()
    {
        $this->addProperty('firstname');
        $this->addProperty('lastname');
        $this->defineDynamicMethods('cleanProperty', 'clean', '', array('firstname'));
        $this->defineDynamicMethods('screwProperty', 'screw');
    }
}

class Contact extends Model\Base
{
    public function cleanProperty($name)
    {
        $this->setProperty($name, null);
    }
}

Contact::setMetaclass(new ContactMetaclass);