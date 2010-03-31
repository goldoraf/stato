<?php

namespace Stato\Model;

require_once __DIR__ . '/TestsHelper.php';

use Event;
use DateTime;

class BaseTest extends TestCase
{
    public function testDynamicGettersAndSetters()
    {
        $e = new Event();
        $e->setTitle('Dinner with John');
        $this->assertEquals('Dinner with John', $e->getTitle());
    }
    
    public function testDynamicMethodOverride()
    {
        $e = new Event();
        $e->setDescription('bla bla');
        $this->assertEquals('hello world', $e->getDescription());
    }
    
    public function testPropertyDirectAccess()
    {
        $e = new Event();
        $e->title = 'Dinner with John';
        $this->assertEquals('Dinner with John', $e->title);
    }
    
    public function testPropertyDirectAccessOverride()
    {
        $e = new Event();
        $e->description = 'bla bla';
        $this->assertEquals('hello world', $e->description);
    }
    
    public function testMissingPropertyDirectRead()
    {
        $this->setExpectedException('\Stato\Model\PropertyMissingException');
        $e = new Event();
        $d = $e->desc;
    }
    
    public function testMissingPropertyDirectWrite()
    {
        $this->setExpectedException('\Stato\Model\PropertyMissingException');
        $e = new Event();
        $e->desc = 'test';
    }
    
    public function testConstructorPropertiesAssignation()
    {
        $e = new Event(array(
            'title' => 'Dinner with John',
            'startAt' => new DateTime('2010-01-01 20:30:00')
        ));
        $this->assertEquals('Dinner with John', $e->getTitle());
    }
    
    public function testMissingPropertiesPassedToConstructor()
    {
        $this->setExpectedException('\Stato\Model\PropertyMissingException');
        $e = new Event(array(
            'title' => 'Dinner with John',
            'startDate' => new DateTime('2010-01-01 20:30:00')
        ));
    }
    
    public function testNewEmptyObjectHasNotChanged()
    {
        $e = new Event();
        $this->assertFalse($e->hasChanged());
    }
    
    public function testPreviouslyEmptyObjectHasChanged()
    {
        $e = new Event();
        $e->setTitle('Dinner with John');
        $this->assertTrue($e->hasChanged());
    }
    
    public function testNewObjectWithArgsHasChanged()
    {
        $e = new Event(array('title' => 'Dinner with John'));
        $this->assertTrue($e->hasChanged());
    }
    
    public function testGetChangedProperties()
    {
        $e = new Event();
        $e->setTitle('Dinner with John');
        $this->assertEquals(array('title'), $e->getChangedProperties());
    }
    
    public function testHasPropertyNotChanged()
    {
        $e = new Event();
        $this->assertFalse($e->hasPropertyChanged('title'));
    }
    
    public function testHasPropertyChanged()
    {
        $e = new Event();
        $e->setTitle('Dinner with John');
        $this->assertTrue($e->hasPropertyChanged('title'));
    }
    
    public function testGetChanges()
    {
        $e = new Event(array('title' => 'Dinner with John'));
        $e->setTitle('Dinner with Jane');
        $this->assertEquals(array('title' => array('Dinner with John', 'Dinner with Jane')), $e->getChanges());
    }
}