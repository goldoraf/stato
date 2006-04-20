<?php

require_once(CORE_DIR.'/view/view.php');

class NumberHelperTest extends HelperTestCase
{   
    public function testNumberWithDelimiter()
    {
        $this->assertEqual('12,345,678', number_with_delimiter('12345678'));
        $this->assertEqual('12 345 678', number_with_delimiter('12345678', ' '));
    }
    
    public function testNumberWithPrecision()
    {
        $this->assertEqual('123.457', number_with_precision('123.456789'));
        $this->assertEqual('123.46', number_with_precision('123.456789', 2));
        $this->assertEqual('123', number_with_precision('123.456789', 0));
    }
}

?>
