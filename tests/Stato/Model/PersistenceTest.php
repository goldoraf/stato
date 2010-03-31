<?php

namespace Stato\Model;

require_once __DIR__ . '/TestsHelper.php';

use Event;
use DateTime;

class PersistenceTest extends TestCase
{
    public function testSave()
    {
        $event = new Event(array('title' => 'Dinner with John'));
        $this->assertNull($event->id);
        $this->assertTrue($event->save());
        $this->assertEquals(1, $event->id);
    }
}