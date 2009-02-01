<?php

require_once dirname(__FILE__) . '/../../../tests/TestsHelper.php';

require_once 'helpers/number.php';

class Stato_NumberHelperTest extends PHPUnit_Framework_TestCase
{
	public function testNumberWithDelimiter()
    {
        $this->assertEquals('12,345,678', number_with_delimiter('12345678'));
        $this->assertEquals('12 345 678', number_with_delimiter('12345678', ' '));
    }
    
    public function testNumberWithPrecision()
    {
        $this->assertEquals('123.457', number_with_precision('123.456789'));
        $this->assertEquals('123.46', number_with_precision('123.456789', 2));
        $this->assertEquals('123', number_with_precision('123.456789', 0));
    }
}
