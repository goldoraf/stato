<?php

namespace Stato\Model;

use Stato\TestCase;

require_once __DIR__ . '/../TestsHelper.php';
require_once __DIR__ . '/models/Event.php';

use Event;
use EventWithoutMeta;
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
    
    public function testDirectPropertyAccess()
    {
        $e = new Event();
        $e->title = 'Dinner with John';
        $this->assertEquals('Dinner with John', $e->title);
    }
    
    public function testDirectPropertyAccessOverride()
    {
        $e = new Event();
        $e->description = 'bla bla';
        $this->assertEquals('hello world', $e->description);
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
}