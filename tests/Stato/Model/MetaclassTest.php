<?php

namespace Stato\Model;

require_once __DIR__ . '/TestsHelper.php';

use Contact, FooMetaclass;

class MetaclassTest extends TestCase
{
    public function testGetSerial()
    {
        $meta = new FooMetaclass();
        $this->assertEquals('id', $meta->getSerial());
    }
    
    public function testGetKey()
    {
        $meta = new FooMetaclass();
        $this->assertEquals(array(new Property('id', Metaclass::SERIAL)), $meta->getKey());
    }
    
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