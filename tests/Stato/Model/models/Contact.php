<?php

use Stato\Model;

class ContactMetadata extends Model\Metadata
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

Contact::setMetadata(new ContactMetadata);