<?php

require_once(CORE_DIR.'/common/common.php');

class DateTest extends UnitTestCase
{
    public function test_date()
    {
        $d = new SDate(2006, 4, 15);
        $this->assertEqual('2006-04-15', $d->__toString());
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
    
    public function test_date_from_array()
    {
        $this->assertEqual(new SDate(1969, 7, 21),
                           SDate::from_array(array('year' => 1969, 'month' => 7, 'day' => 21)));
    }
    
    public function test_date_time()
    {
        $d = new SDateTime(1969, 7, 21, 20, 35, 05);
        $this->assertEqual('1969-07-21 20:35:05', $d->__toString());
        $d = new SDateTime(1969, 7, 21, 20, 65, 05);
        $this->assertEqual('1969-07-21 21:05:05', $d->__toString());
        $d = new SDateTime(1969, 7, 21, 20, 125, 05);
        $this->assertEqual('1969-07-21 22:05:05', $d->__toString());
    }
    
    public function test_date_time_from_array()
    {
        $this->assertEqual(new SDateTime(1969, 7, 21),
                           SDateTime::from_array(array('year' => 1969, 'month' => 7, 'day' => 21)));
        $this->assertEqual(new SDateTime(1969, 7, 21, 20),
                           SDateTime::from_array(array('year' => 1969, 'month' => 7, 'day' => 21, 'hour' => 20)));
        $this->assertEqual(new SDateTime(1969, 7, 21, 20, 35),
                           SDateTime::from_array(array('year' => 1969, 'month' => 7, 'day' => 21, 'hour' => 20, 'min' => 35)));
        $this->assertEqual(new SDateTime(1969, 7, 21, 20, 35, 05),
                           SDateTime::from_array(array('year' => 1969, 'month' => 7, 'day' => 21, 'hour' => 20, 'min' => 35, 'sec' => 05)));
    }
    
    public function test_date_iso8601()
    {
        $d = new SDate(1969, 7, 21);
        $this->assertEqual('1969-07-21', $d->__toString());
        $this->assertEqual('19690721T00:00:00', $d->to_iso8601());
        $d = new SDateTime(1969, 7, 21, 20, 35, 05);
        $this->assertEqual('1969-07-21 20:35:05', $d->__toString());
        $this->assertEqual('19690721T20:35:05', $d->to_iso8601());
    }
    
    public function test_parsing()
    {
        $this->assertEqual(new SDate(1969, 7, 21),
                           SDate::parse('1969-07-21'));
        $this->assertEqual(new SDateTime(1969, 7, 21, 20, 35, 05),
                           SDateTime::parse('19690721T20:35:05'));
        $this->assertEqual(new SDateTime(1969, 7, 21, 20, 35, 05),
                           SDateTime::parse('1969-07-21 20:35:05'));
    }
    
    public function test_leap_year()
    {
        $date = new SDate(2000, 1, 1);
        $this->assertTrue($date->is_leap());
        /*$date = new SDate(1900, 1, 1);
        $this->assertFalse($date->is_leap());*/
        $date = new SDate(2004, 1, 1);
        $this->assertTrue($date->is_leap());
    }
    
    public function test_step()
    {
        $date = new SDate(2006, 09, 13);
        $this->assertEqual($date->step(1), new SDate(2006, 09, 14));
        $this->assertEqual($date->step(-1), new SDate(2006, 09, 12));
    }
    
    public function test_alias_now()
    {
        $this->assertEqual(SDateTime::now(), SDateTime::today());
    }
}

?>
