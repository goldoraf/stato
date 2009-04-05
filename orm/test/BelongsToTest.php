<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class BelongsToTest extends ActiveTestCase
{
    public $fixtures = array('profiles', 'employes');
    public $models = array('dependent_company_1', 'dependent_company_2', 'dependent_company_3');
    
    public function test_belongs_to()
    {
        $profile = Profile::$objects->get(1);
        $employe = Employe::$objects->get(1);
        $this->assertEquals($profile->employe->lastname, $employe->lastname);
    }
    
    public function test_assignment()
    {
        $profile = new Profile(array('cv'=>'GNU expert'));
        $employe = new Employe(array('lastname'=>'Richard', 'firstname'=>'Stallman'));
        $this->assertTrue($profile->employe->is_null());
        $profile->employe = $employe;
        $this->assertFalse($profile->employe->is_null());
        $profile->save();
        $this->assertEquals($employe->id, $profile->employe_id);
    }
    
    public function test_null_assignment()
    {
        $profile = Profile::$objects->get(1);
        $this->assertFalse($profile->employe->is_null());
        $profile->employe = null;
        $this->assertTrue($profile->employe->is_null());
        $profile->save();
        $profile2 = Profile::$objects->get(1);
        $this->assertTrue($profile2->employe->is_null());
    }
    
    public function test_assignment_before_parent_saved()
    {
        $profile = Profile::$objects->get(1);
        $employe = new Employe(array('lastname'=>'Max', 'firstname'=>'Payne'));
        $profile->employe = $employe;
        $this->assertEquals($profile->employe->lastname, $employe->lastname);
        $this->assertTrue($employe->is_new_record());
        $profile->save();
        $this->assertFalse($employe->is_new_record());
        $this->assertEquals($employe->id, $profile->employe_id);
    }
    
    public function test_assignment_before_child_saved()
    {
        $employe = Employe::$objects->get(1);
        $profile = new Profile(array('cv'=>'Mozilla expert'));
        $profile->employe = $employe;
        $this->assertTrue($profile->is_new_record());
        $profile->save();
        $this->assertFalse($profile->is_new_record());
        $this->assertFalse($employe->is_new_record());
        $this->assertEquals($employe->id, $profile->employe_id);
    }
    
    public function test_assignment_before_either_saved()
    {
        $employe = new Employe(array('lastname'=>'Max', 'firstname'=>'Payne'));
        $profile = new Profile(array('cv'=>'Lone private'));
        $profile->employe = $employe;
        $this->assertTrue($profile->is_new_record());
        $this->assertTrue($employe->is_new_record());
        $profile->save();
        $this->assertFalse($profile->is_new_record());
        $this->assertFalse($employe->is_new_record());
        $this->assertEquals($employe->id, $profile->employe_id);
    }
}
