<?php

namespace Stato\Model;

use Stato\TestCase;

require_once __DIR__ . '/../TestsHelper.php';
require_once __DIR__ . '/models/Contact.php';

use Contact;

class MetaclassTest extends TestCase
{
    public function testDefineDynamicMethods()
    {
        $c = new Contact();
        $c->setFirstname('John');
        $c->cleanFirstname();
        $this->assertNull($c->getFirstname());
    }
    
    public function testUndefinedDynamicMethod()
    {
        $this->setExpectedException('\Stato\Model\MethodMissingException');
        $c = new Contact();
        $c->cleanLastname();
    }
    
    public function testUndefinedMethodTarget()
    {
        $this->setExpectedException('\Stato\Model\MethodMissingTargetException');
        $c = new Contact();
        $c->screwLastname();
    }
    
    public function testUndefinedProperty()
    {
        $this->setExpectedException('\Stato\Model\PropertyMissingException');
        $m = new Metaclass();
        $m->addProperty('foo');
        $m->defineDynamicMethods('getProperty', 'get', '', array('bar'));
    }
}