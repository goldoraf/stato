<?php

require_once(CORE_DIR.'/common/common.php');

class DateTest extends UnitTestCase
{
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
}

?>
