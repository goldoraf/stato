<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

class DateTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        date_default_timezone_set('Europe/Paris');
    }
    
    public function test_date()
    {
        $d = new SDate(2006, 4, 15);
        $this->assertEquals('2006-04-15', $d->__toString());
        $d = new SDate(2006, 4, 0);
        $this->assertEquals('2006-03-31', $d->__toString());
        $d = new SDate(2006, 4, -1);
        $this->assertEquals('2006-03-30', $d->__toString());
        $d = new SDate(2006, 3, 32);
        $this->assertEquals('2006-04-01', $d->__toString());
        $d = new SDate(2006, 3, 61);
        $this->assertEquals('2006-04-30', $d->__toString());
        $d = new SDate(2006, 3, 62);
        $this->assertEquals('2006-05-01', $d->__toString());
    }
    
    public function test_date_from_array()
    {
        $this->assertEquals(new SDate(1969, 7, 21),
                           SDate::from_array(array('year' => 1969, 'month' => 7, 'day' => 21)));
    }
    
    public function test_date_time()
    {
        $d = new SDateTime(1969, 7, 21, 20, 35, 05);
        $this->assertEquals('1969-07-21 20:35:05', $d->__toString());
        $d = new SDateTime(1969, 7, 21, 20, 65, 05);
        $this->assertEquals('1969-07-21 21:05:05', $d->__toString());
        $d = new SDateTime(1969, 7, 21, 20, 125, 05);
        $this->assertEquals('1969-07-21 22:05:05', $d->__toString());
    }
    
    public function test_date_time_from_array()
    {
        $this->assertEquals(new SDateTime(1969, 7, 21),
                           SDateTime::from_array(array('year' => 1969, 'month' => 7, 'day' => 21)));
        $this->assertEquals(new SDateTime(1969, 7, 21, 20),
                           SDateTime::from_array(array('year' => 1969, 'month' => 7, 'day' => 21, 'hour' => 20)));
        $this->assertEquals(new SDateTime(1969, 7, 21, 20, 35),
                           SDateTime::from_array(array('year' => 1969, 'month' => 7, 'day' => 21, 'hour' => 20, 'min' => 35)));
        $this->assertEquals(new SDateTime(1969, 7, 21, 20, 35, 05),
                           SDateTime::from_array(array('year' => 1969, 'month' => 7, 'day' => 21, 'hour' => 20, 'min' => 35, 'sec' => 05)));
    }
    
    public function test_parsing()
    {
        $this->assertEquals(new SDate(1969, 7, 21),
                           SDate::parse('1969-07-21'));
        $this->assertEquals(new SDate(1969, 7, 21),
                           SDate::parse('19690721'));
        $this->assertEquals(new SDateTime(1969, 7, 21, 20, 35, 05),
                           SDateTime::parse('19690721T20:35:05'));
        $this->assertEquals(new SDateTime(1969, 7, 21, 20, 35, 05),
                           SDateTime::parse('1969-07-21 20:35:05'));
    }
    
    public function test_leap_year()
    {
        $date = new SDate(2000, 1, 1);
        $this->assertTrue($date->is_leap());
        $date = new SDate(2004, 1, 1);
        $this->assertTrue($date->is_leap());
    }
    
    public function test_step()
    {
        $date = new SDate(2006, 9, 13);
        $this->assertEquals($date->step(1), new SDate(2006, 9, 14));
        $this->assertEquals($date->step(-1), new SDate(2006, 9, 12));
    }
    
    public function test_modify()
    {
        $date = new SDate(2006, 9, 13);
        $this->assertEquals($date->modify('+1 day'), new SDate(2006, 9, 14));
        $this->assertEquals($date->modify('+1 week'), new SDate(2006, 9, 21));
    }
    
    public function test_new_offset()
    {
        $date = new SDateTime(2006, 9, 13, 20, 25, 05);
        $this->assertEquals('2006-09-13 20:25:05', $date->__toString());
        $this->assertEquals('2006-09-13 20:25:05', $date->new_offset(- 6*3600)->__toString());
        $this->assertEquals('2006-09-13 14:25:05', $date->new_offset(- 6*3600)->to_local()->__toString());
    }
    
    public function test_alias_now()
    {
        $this->assertEquals(SDateTime::now(), SDateTime::today());
    }
    
    public function test_date_iso8601()
    {
        $d = new SDate(1969, 7, 21);
        $this->assertEquals('1969-07-21', $d->__toString());
        $this->assertEquals('19690721T00:00:00', $d->to_iso8601());
        $d = new SDateTime(1969, 7, 21, 20, 35, 05);
        $this->assertEquals('1969-07-21 20:35:05', $d->__toString());
        $this->assertEquals('19690721T20:35:05', $d->to_iso8601());
    }
    
    public function test_localize()
    {
        setlocale(LC_ALL, 'en_US');
        $d = new SDate(1969, 7, 21);
        $this->assertEquals($d->localize(), '07/21/69');
        $d = new SDateTime(1969, 7, 21, 20, 35, 05);
        $this->assertEquals($d->localize(), '07/21/69 20:35:05');
    }
}

