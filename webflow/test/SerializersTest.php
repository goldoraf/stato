<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

class SerializersTest extends StatoTestCase
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
        $this->assertDomEquals('<result>test</result>', $s->serialize('test'));
        $this->assertDomEquals('<result><value>test</value></result>', $s->serialize(array('test')));
        $this->assertDomEquals('<result><key>test</key></result>', $s->serialize(array('key'=>'test')));
    }
}
