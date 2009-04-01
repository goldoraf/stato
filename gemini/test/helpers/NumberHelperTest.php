<?php

require_once dirname(__FILE__) . '/../../../test/tests_helper.php';

class NumberHelperTest extends StatoTestCase
{   
    public function test_number_with_delimiter()
    {
        $this->assertEquals('12,345,678', number_with_delimiter('12345678'));
        $this->assertEquals('12 345 678', number_with_delimiter('12345678', ' '));
    }
    
    public function test_number_with_precision()
    {
        $this->assertEquals('123.457', number_with_precision('123.456789'));
        $this->assertEquals('123.46', number_with_precision('123.456789', 2));
        $this->assertEquals('123', number_with_precision('123.456789', 0));
    }
}

