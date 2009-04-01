<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

require_once STATO_CORE_PATH.'/gemini/lib/serializers.php';

class ProductWithExclude extends SActiveRecord
{
    public static $objects;
    public static $table_name = 'products';
    
    public function serializable_form($options = array())
    {
        return parent::serializable_form(array('exclude' => 'price'));
    }
}

class ProfileWithInclude extends SActiveRecord
{
    public static $objects;
    public static $table_name = 'profiles';
    public static $relationships = array('employe' => 'belongs_to');
    
    public function serializable_form($options = array())
    {
        return parent::serializable_form(array('include' => 'employe'));
    }
}

class CompanyWithInclude extends SActiveRecord
{
    public static $objects;
    public static $table_name = 'companies';
    public static $relationships = array('employes' => array('assoc_type' => 'has_many', 'foreign_key' => 'company_id'));
    
    public function serializable_form($options = array())
    {
        return parent::serializable_form(array('include' => 'employes'));
    }
}

class SerializationTest extends ActiveTestCase
{
    public $fixtures = array('products', 'contracts', 'employes', 'profiles', 'companies');
    
    public function test_active_record_to_xml()
    {
        $s = new SXmlSerializer();
        $r = new Product(array('id' => 1234, 'name' => 'CD-R', 'price' => 9.90, 'company_id' => 28));
        $this->assertDomEquals($s->serialize($r),
            '<product>
                <id>1234</id>
                <name>CD-R</name>
                <price>9.9</price>
              </product>'); 
    }
    
    public function test_active_record_to_json()
    {
        $s = new SJsonSerializer();
        $r = new Product(array('id' => 1234, 'name' => 'CD-R', 'price' => 9.90, 'company_id' => 28));
        $this->assertEquals('{"id":"1234","name":"CD-R","price":"9.9"}', $s->serialize($r));
    }
    
    public function test_queryset_to_xml()
    {
        $s = new SXmlSerializer();
        $q = Contract::$objects->all();
        $this->assertDomEquals($s->serialize($q),
            '<contracts>
                <contract>
                  <id>1</id>
                  <code>AAA-ZZZ-FFF</code>
                  <date>2005-12-02</date>
                </contract>
                <contract>
                  <id>2</id>
                  <code>PPP-HHH-PPP</code>
                  <date>2005-12-01</date>
                </contract>
              </contracts>');
    }
    
    public function test_queryset_to_json()
    {
        $s = new SJsonSerializer();
        $q = Contract::$objects->all();
        $this->assertEquals($s->serialize($q), '[{"id":"1","code":"AAA-ZZZ-FFF","date":"2005-12-02"},{"id":"2","code":"PPP-HHH-PPP","date":"2005-12-01"}]');
    }
    
    public function test_active_record_with_exclude_to_xml()
    {
        $s = new SXmlSerializer();
        $r = new ProductWithExclude(array('id' => 1234, 'name' => 'CD-R', 'price' => 9.90, 'company_id' => 28));
        $this->assertDomEquals($s->serialize($r),
            '<product-with-exclude>
                <id>1234</id>
                <name>CD-R</name>
              </product-with-exclude>'); 
    }
    
    public function test_active_record_with_exclude_to_json()
    {
        $s = new SJsonSerializer();
        $r = new ProductWithExclude(array('id' => 1234, 'name' => 'CD-R', 'price' => 9.90, 'company_id' => 28));
        $this->assertEquals('{"id":"1234","name":"CD-R"}', $s->serialize($r));
    }
    
    public function test_active_record_with_belongs_to_include_to_xml()
    {
        SMapper::add_manager_to_class('ProfileWithInclude');
        $s = new SXmlSerializer();
        $r = ProfileWithInclude::$objects->get(1);
        $this->assertDomEquals($s->serialize($r),
            '<profile-with-include>
                <id>1</id>
                <cv>blablabla</cv>
                <employe>
                  <id>1</id>
                  <firstname>John</firstname>
                  <lastname>Doe</lastname>
                  <function>programmer</function>
                  <date-of-birth>1969-07-21</date-of-birth>
                </employe>
              </profile-with-include>');
    }
    
    public function test_active_record_with_belongs_to_include_to_json()
    {
        SMapper::add_manager_to_class('ProfileWithInclude');
        $s = new SJsonSerializer();
        $r = ProfileWithInclude::$objects->get(1);
        $this->assertDomEquals($s->serialize($r),
            '{"id":"1","cv":"blablabla","employe":{"id":"1","firstname":"John","lastname":"Doe","function":"programmer","date_of_birth":"1969-07-21"}}');
    }
    
    public function test_active_record_with_has_many_include_to_xml()
    {
        SMapper::add_manager_to_class('CompanyWithInclude');
        $s = new SXmlSerializer();
        $r = CompanyWithInclude::$objects->get(1);
        $this->assertDomEquals($s->serialize($r),
            '<company-with-include>
                <id>1</id>
                <name>World Company</name>
                <employes>
                  <employe>
                    <id>1</id>
                    <firstname>John</firstname>
                    <lastname>Doe</lastname>
                    <function>programmer</function>
                    <date-of-birth>1969-07-21</date-of-birth>
                  </employe>
                  <employe>
                    <id>2</id>
                    <firstname>Bridget</firstname>
                    <lastname>Jones</lastname>
                    <function>assistant</function>
                    <date-of-birth>1961-04-12</date-of-birth>
                  </employe>
                </employes>
              </company-with-include>');
    }
    
    public function test_active_record_with_has_many_include_to_json()
    {
        SMapper::add_manager_to_class('CompanyWithInclude');
        $s = new SJsonSerializer();
        $r = CompanyWithInclude::$objects->get(1);
        $this->assertDomEquals($s->serialize($r),
            '{"id":"1","name":"World Company","employes":[{"id":"1","firstname":"John","lastname":"Doe","function":"programmer","date_of_birth":"1969-07-21"},{"id":"2","firstname":"Bridget","lastname":"Jones","function":"assistant","date_of_birth":"1961-04-12"}]}');
    }
}



