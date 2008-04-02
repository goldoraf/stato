<?php

class SerializersTestCase extends StatoTestCase
{
    public function test_simple_values_to_json()
    {
        $s = new SJsonSerializer();
        $this->assertEqual('"test"', $s->serialize('test'));
        $this->assertEqual('["test"]', $s->serialize(array('test')));
        $this->assertEqual('{"key":"test"}', $s->serialize(array('key'=>'test')));
    }
    
    public function test_simple_values_to_xml()
    {
        $s = new SXmlSerializer();
        $this->assertDomEqual('<result>test</result>', $s->serialize('test'));
        $this->assertDomEqual('<result><value>test</value></result>', $s->serialize(array('test')));
        $this->assertDomEqual('<result><key>test</key></result>', $s->serialize(array('key'=>'test')));
    }
}

?>