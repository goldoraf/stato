<?php

class MockActiveRecord
{
    private $values = array
    (
        'id'   => 1234,
        'name' => 'raphael'
    );
    
    public function serializable_form()
    {
        $obj = new stdClass;
        foreach ($this->values as $k => $v) $obj->$k = $v;
        return $obj;
    }
}

class SerializersTestCase extends XmlTestCase
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
    
    public function test_active_record_to_xml()
    {
        $s = new SXmlSerializer();
        $this->assertDomEqual('<mock_active_record><id>1234</id><name>raphael</name></mock_active_record>',
                              $s->serialize(new MockActiveRecord));
    }
    
    public function test_active_record_to_json()
    {
        $s = new SJsonSerializer();
        $this->assertDomEqual('{"id":1234,"name":"raphael"}',
                              $s->serialize(new MockActiveRecord));
    }
}

?>