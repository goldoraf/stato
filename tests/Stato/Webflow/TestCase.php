<?php



class Stato_Webflow_TestCase extends PHPUnit_Framework_TestCase
{
    protected function assertDomEquals($str1, $str2)
    {
        $this->assertXmlStringEqualsXmlString("<root>$str1</root>", "<root>$str2</root>");
    }
}