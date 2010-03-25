<?php

namespace Stato\Model;

use Stato\TestCase;
use Stato\TestEnv;

require_once __DIR__ . '/../TestsHelper.php';
require_once __DIR__ . '/models/Event.php';

use Event;
use DateTime;

class PersistenceTest extends TestCase
{
    public function setup()
    {
        Repository::setup('default', TestEnv::getDbConfig());
    }
    
    public function testGetRepository()
    {
        $this->assertEquals(Event::getRepository(), Repository::get('default'));
    }
    
    public function testSave()
    {
        $event = new Event(array('title' => 'Dinner with John'));
        $this->assertNull($event->id);
        $this->assertTrue($event->save());
        $this->assertEquals(1, $event->id);
    }
}