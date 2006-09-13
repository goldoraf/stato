<?php

require_once(CORE_DIR.'/common/common.php');

class DateTest extends UnitTestCase
{
    public function testDateConstruct()
    {
        $d = new SDate(2006, 4, 0);
        $this->assertEqual('2006-03-31', $d->__toString());
        $d = new SDate(2006, 4, -1);
        $this->assertEqual('2006-03-30', $d->__toString());
        $d = new SDate(2006, 3, 32);
        $this->assertEqual('2006-04-01', $d->__toString());
        $d = new SDate(2006, 3, 61);
        $this->assertEqual('2006-04-30', $d->__toString());
        $d = new SDate(2006, 3, 62);
        $this->assertEqual('2006-05-01', $d->__toString());
    }
    
    public function testDate()
    {
        $d = new SDate(1969, 7, 21);
        $this->assertEqual('1969-07-21', $d->__toString());
        $this->assertEqual('19690721T00:00:00', $d->toIso8601());
    }
    
    public function testDateTime()
    {
        $d = new SDateTime(1969, 7, 21, 20, 35, 05);
        $this->assertEqual('1969-07-21 20:35:05', $d->__toString());
        $this->assertEqual('19690721T20:35:05', $d->toIso8601());
    }
    
    public function testParsing()
    {
        $this->assertEqual(new SDate(1969, 7, 21),
                           SDate::parse('1969-07-21'));
        $this->assertEqual(new SDateTime(1969, 7, 21, 20, 35, 05),
                           SDateTime::parse('19690721T20:35:05'));
        $this->assertEqual(new SDateTime(1969, 7, 21, 20, 35, 05),
                           SDateTime::parse('1969-07-21 20:35:05'));
    }
    
    public function testLeapYear()
    {
        $date = new SDate(2000, 1, 1);
        $this->assertTrue($date->isLeap());
        /*$date = new SDate(1900, 1, 1);
        $this->assertFalse($date->isLeap());*/
        $date = new SDate(2004, 1, 1);
        $this->assertTrue($date->isLeap());
    }
    
    public function testStep()
    {
        $date = new SDate(2006, 09, 13);
        $this->assertEqual($date->step(1), new SDate(2006, 09, 14));
        $this->assertEqual($date->step(-1), new SDate(2006, 09, 12));
    }
}

?>
