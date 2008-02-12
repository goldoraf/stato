<?php

require_once STATO_CORE_PATH.'/gemini/lib/serializers.php';

class SerializationTest extends ActiveTestCase
{
    public $fixtures = array('products', 'contracts', 'employes');
    
    public function test_active_record_to_xml()
    {
        $s = new SXmlSerializer();
        $r = new Product(array('id' => 1234, 'name' => 'CD-R', 'price' => 9.90, 'company_id' => 28));
        $this->assertDomEqual($s->serialize($r),
            '<product>
                <id>1234</id>
                <name>CD-R</name>
                <price>9.9</price>
                <company-id>28</company-id>
              </product>'); 
    }
    
    public function test_active_record_to_json()
    {
        $s = new SJsonSerializer();
        $r = new Product(array('id' => 1234, 'name' => 'CD-R', 'price' => 9.90, 'company_id' => 28));
        $this->assertEqual('{"id":"1234","name":"CD-R","price":"9.9","company_id":"28"}', $s->serialize($r));
    }
    
    public function test_queryset_to_xml()
    {
        $s = new SXmlSerializer();
        $q = Contract::$objects->all();
        $this->assertDomEqual($s->serialize($q),
            '<contract>
                <contract>
                  <id>1</id>
                  <client-id>1</client-id>
                  <code>AAA-ZZZ-FFF</code>
                  <date>2005-12-02</date>
                </contract>
                <contract>
                  <id>2</id>
                  <client-id>2</client-id>
                  <code>PPP-HHH-PPP</code>
                  <date>2005-12-01</date>
                </contract>
              </contract>');
    }
    
    public function test_queryset_to_json()
    {
        $s = new SJsonSerializer();
        $q = Contract::$objects->all();
        $this->assertEqual($s->serialize($q), '[{"id":"1","client_id":"1","code":"AAA-ZZZ-FFF","date":"2005-12-02"},{"id":"2","client_id":"2","code":"PPP-HHH-PPP","date":"2005-12-01"}]');
    }
}



?>
