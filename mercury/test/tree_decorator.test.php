<?php

class TreeDecoratorTest extends ActiveTestCase
{
    public $fixtures = array('pages');
    public $use_instantiated_fixtures = true;
    
    public function test_has_child()
    {
        $this->assertTrue($this->pages['page_1']->children->count() != 0);
        $this->assertTrue($this->pages['page_2']->children->count() != 0);
        $this->assertFalse($this->pages['page_3']->children->count() != 0);
        $this->assertFalse($this->pages['page_4']->children->count() != 0);
    }
    
    public function test_children()
    {
        $this->assertEqual($this->pages['page_1']->children->ids(), array($this->pages['page_2']->id, $this->pages['page_4']->id));
        $this->assertEqual($this->pages['page_2']->children->ids(), array($this->pages['page_3']->id));
        $this->assertEqual($this->pages['page_3']->children->ids(), array());
        $this->assertEqual($this->pages['page_4']->children->ids(), array());
    }
    
    public function test_has_parent()
    {
        $this->assertTrue($this->pages['page_1']->parent->is_null());
        $this->assertFalse($this->pages['page_2']->parent->is_null());
        $this->assertFalse($this->pages['page_3']->parent->is_null());
        $this->assertFalse($this->pages['page_4']->parent->is_null());
    }
    
    public function test_parent()
    {
        $this->assertEqual($this->pages['page_2']->parent->id, $this->pages['page_1']->id);
        $this->assertEqual($this->pages['page_2']->parent->id, $this->pages['page_4']->parent->id);
        $this->assertNull($this->pages['page_1']->parent->target());
    }
    
    public function test_delete()
    {
        $this->assertEqual(6, Page::$objects->count());
        $this->pages['page_1']->delete();
        $this->assertEqual(2, Page::$objects->count());
        $this->pages['page_1_1']->delete();
        $this->pages['page_1_2']->delete();
        $this->assertEqual(0, Page::$objects->count());
    }
    
    public function test_create()
    {
        $new = $this->pages['page_1']->children->create();
        $this->assertEqual($new->parent->name, $this->pages['page_1']->name);
        $this->assertEqual(3, $this->pages['page_1']->children->count());
    }
    
    public function test_ancestors()
    {
        $this->assertEqual(array(), $this->pages['page_1']->ancestors());
        $this->assertEqual(array($this->pages['page_1']->name), $this->get_names($this->pages['page_2']->ancestors()));
        $this->assertEqual(array($this->pages['page_2']->name, $this->pages['page_1']->name), $this->get_names($this->pages['page_3']->ancestors()));
        $this->assertEqual(array($this->pages['page_1']->name), $this->get_names($this->pages['page_4']->ancestors()));
        $this->assertEqual(array(), $this->pages['page_1_1']->ancestors());
        $this->assertEqual(array(), $this->pages['page_1_2']->ancestors());
    }
    
    public function test_root()
    {
        $this->assertEqual($this->pages['page_1']->name, $this->pages['page_1']->root()->name);
        $this->assertEqual($this->pages['page_1']->name, $this->pages['page_2']->root()->name);
        $this->assertEqual($this->pages['page_1']->name, $this->pages['page_3']->root()->name);
        $this->assertEqual($this->pages['page_1']->name, $this->pages['page_4']->root()->name);
        $this->assertEqual($this->pages['page_1_1']->name, $this->pages['page_1_1']->root()->name);
        $this->assertEqual($this->pages['page_1_2']->name, $this->pages['page_1_2']->root()->name);
    }
    
    public function test_roots()
    {
        $this->assertEqual(array($this->pages['page_1']->name, $this->pages['page_1_1']->name, $this->pages['page_1_2']->name), 
                           $this->get_names($this->pages['page_1']->roots()));
    }
    
    public function test_siblings()
    {
        $this->assertEqual(array($this->pages['page_1_1']->name, $this->pages['page_1_2']->name), 
                           $this->get_names($this->pages['page_1']->siblings()));
        $this->assertEqual(array($this->pages['page_4']->name), 
                           $this->get_names($this->pages['page_2']->siblings()));
        $this->assertEqual(array(), 
                           $this->get_names($this->pages['page_3']->siblings()));
        $this->assertEqual(array($this->pages['page_2']->name), 
                           $this->get_names($this->pages['page_4']->siblings()));
    }
    
    public function test_self_and_siblings()
    {
        $this->assertEqual(array($this->pages['page_1']->name, $this->pages['page_1_1']->name, $this->pages['page_1_2']->name), 
                           $this->get_names($this->pages['page_1']->self_and_siblings()));
        $this->assertEqual(array($this->pages['page_2']->name, $this->pages['page_4']->name), 
                           $this->get_names($this->pages['page_2']->self_and_siblings()));
        $this->assertEqual(array($this->pages['page_3']->name), 
                           $this->get_names($this->pages['page_3']->self_and_siblings()));
        $this->assertEqual(array($this->pages['page_2']->name, $this->pages['page_4']->name), 
                           $this->get_names($this->pages['page_4']->self_and_siblings()));
    }
    
    private function get_names($records)
    {
        $names = array();
        foreach ($records as $r) $names[] = $r->name;
        return $names;
    }
}

?>
