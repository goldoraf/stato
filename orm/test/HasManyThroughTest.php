<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class HasManyThroughTest extends ActiveTestCase
{
    public $fixtures = array('companies', 'employes', 'profiles');
    
    public function test_has_many_join_model()
    {
        $comp = Company::$objects->get(1);
        $profiles = $comp->profiles->all()->to_array();
        $this->assertEquals(2, count($profiles));
        $this->assertEquals('blablabla', $profiles[0]->cv);
        $this->assertEquals('xxx', $profiles[1]->cv);
    }
    
    public function test_belongs_to_join_model()
    {
    
    }
}
