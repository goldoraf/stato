<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class ManyToManyTest extends ActiveTestCase
{
    public $fixtures = array('developers', 'projects', 'developers_projects', 'clients');
    
    public function test_basic()
    {
        $ben = Developer::$objects->get(1);
        $this->assertEquals(2, $ben->projects->count());
        $proj = Project::$objects->get(1);
        $this->assertEquals(1, $proj->developers->count());
    }
    
    public function test_queryset_with_includes_fails_because_of_non_loaded_associations_meta()
    {
        SMapper::reset_cache(); // we reset so metas will be reloaded without associations metas loaded...
        $ben = Developer::$objects->get(1);
        $projs = $ben->projects->includes('client')->to_array();
        $this->assertEquals('apple', $projs[0]->client->name);
        $this->assertEquals('ibm', $projs[1]->client->name);
    }
    
    public function test_add()
    {
        $richard = Developer::$objects->get(2);
        $proj = Project::$objects->get(1);
        $this->assertEquals(1, $richard->projects->count());
        $this->assertEquals(1, $proj->developers->count());
        $richard->projects->add($proj);
        $this->assertEquals(2, $richard->projects->count());
        $this->assertEquals(2, $proj->developers->count());
    }
    
    public function test_add_before_save()
    {
        $nb_devels = Developer::$objects->count();
        $nb_projs  = Project::$objects->count();
        $peter = new Developer(array('name' => 'peter'));
        $proj1 = new Project(array('name' => 'WebNuked2.0'));
        $proj2 = new Project(array('name' => 'TotalWebInnov'));
        $peter->projects->add($proj1);
        $peter->projects->add($proj2);
        $this->assertTrue($peter->is_new_record());
        $this->assertTrue($proj1->is_new_record());
        $this->assertEquals(2, $peter->projects->count());
        $this->assertEquals($nb_projs, Project::$objects->count());
        $peter->save();
        $this->assertFalse($peter->is_new_record());
        $this->assertFalse($proj1->is_new_record());
        $this->assertEquals($nb_devels+1, Developer::$objects->count());
        $this->assertEquals($nb_projs+2, Project::$objects->count());
        $this->assertEquals(2, $peter->projects->count());
        $peter2 = Developer::$objects->get($peter->id);
        $this->assertEquals(2, $peter2->projects->count());
    }
    
    public function test_create()
    {
        $richard = Developer::$objects->get(2);
        $proj = $richard->projects->create(array('name' => 'PlzNotAnotherRecursiveAcronym'));
        $projects = $richard->projects->all()->to_array();
        $this->assertEquals($projects[1]->name, $proj->name);
        $this->assertFalse($proj->is_new_record());
    }
    
    public function test_delete()
    {
        $ben = Developer::$objects->get(1);
        $proj = Project::$objects->get(1);
        $this->assertEquals(2, $ben->projects->count());
        $this->assertEquals(1, $proj->developers->count());
        $ben->projects->delete($proj);
        $this->assertEquals(1, $ben->projects->count());
        $ben2 = Developer::$objects->get(1);
        $this->assertEquals(1, $ben2->projects->count());
        $proj2 = Project::$objects->get(1);
        $this->assertEquals(0, $proj2->developers->count());
    }
    
    public function test_clear()
    {
        $richard = Developer::$objects->get(2);
        $richard->projects->clear();
        $this->assertEquals(0, $richard->projects->count());
    }
}
