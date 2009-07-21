<?php

class StatoTestCase extends PHPUnit_Framework_TestCase
{
    protected function assertNothingThrown()
    {
        return $this->assertTrue(true);
    }
    
    protected function assertDomEquals($str1, $str2)
    {
        if (preg_match('/^<\?xml version="1.0"(.*)\?>(.*)$/sm', $str1, $matches)) $str1 = $matches[2];
        if (preg_match('/^<\?xml version="1.0"(.*)\?>(.*)$/sm', $str2, $matches)) $str2 = $matches[2];
        $this->assertXmlStringEqualsXmlString("<root>$str1</root>", "<root>$str2</root>");
    }
}
