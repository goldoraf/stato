<?php

class SerializersTest extends PHPUnit_Framework_TestCase
{
    public function test_simple_values_to_json()
    {
        $s = new SJsonSerializer();
        $this->assertEquals('"test"', $s->serialize('test'));
        $this->assertEquals('["test"]', $s->serialize(array('test')));
        $this->assertEquals('{"key":"test"}', $s->serialize(array('key'=>'test')));
    }
    
    public function test_simple_values_to_xml()
    {
        $s = new SXmlSerializer();
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="utf-8"?><result>test</result>', $s->serialize('test'));
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="utf-8"?><result><value>test</value></result>', $s->serialize(array('test')));
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="utf-8"?><result><key>test</key></result>', $s->serialize(array('key'=>'test')));
    }
}

?>
