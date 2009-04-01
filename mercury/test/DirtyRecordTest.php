<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

class DirtyRecordTest extends ActiveTestCase
{
    public $fixtures = array('posts');
    public $models = array('dirty_post');
    
    public function test_attribute_changes()
    {
        $post = new DirtyPost();
        $this->assertFalse($post->has_changed());
        $this->assertEquals(array(), $post->changed());
        $this->assertEquals(array(), $post->changes());
        $this->assertFalse($post->title_has_changed());
        $this->assertNull($post->title_change());
        $this->assertNull($post->title_was());
        
        $post->title = 'My first post';
        $this->assertTrue($post->has_changed());
        $this->assertEquals(array('title'), $post->changed());
        $this->assertEquals(array('title' => array(null, 'My first post')), $post->changes());
        $this->assertTrue($post->title_has_changed());
        $this->assertEquals(array(null, 'My first post'), $post->title_change());
        $this->assertNull($post->title_was());
        
        $post->save();
        $this->assertFalse($post->has_changed());
        $this->assertEquals(array(), $post->changed());
        $this->assertEquals(array(), $post->changes());
        $this->assertFalse($post->title_has_changed());
        $this->assertNull($post->title_change());
        $this->assertEquals('My first post', $post->title_was());
        
        $post->title = 'My first post';
        $this->assertFalse($post->has_changed());
        $this->assertEquals(array(), $post->changed());
        $this->assertEquals(array(), $post->changes());
        $this->assertFalse($post->title_has_changed());
        $this->assertNull($post->title_change());
        $this->assertEquals('My first post', $post->title_was());
        
        $post->title = 'My second post';
        $this->assertTrue($post->has_changed());
        $this->assertEquals(array('title'), $post->changed());
        $this->assertEquals(array('title' => array('My first post', 'My second post')), $post->changes());
        $this->assertTrue($post->title_has_changed());
        $this->assertEquals(array('My first post', 'My second post'), $post->title_change());
        $this->assertEquals('My first post', $post->title_was());
    }
    
    public function test_attribute_changes_after_loading()
    {
        $post = DirtyPost::$objects->get(1);
        $this->assertFalse($post->has_changed());
        $this->assertEquals(array(), $post->changed());
        $this->assertEquals(array(), $post->changes());
        $this->assertFalse($post->title_has_changed());
        $this->assertNull($post->title_change());
        $this->assertEquals('Frameworks : A new hope...', $post->title_was());
        
        $post->title = 'My second post';
        $this->assertTrue($post->has_changed());
        $this->assertEquals(array('title'), $post->changed());
        $this->assertEquals(array('title' => array('Frameworks : A new hope...', 'My second post')), $post->changes());
        $this->assertTrue($post->title_has_changed());
        $this->assertEquals(array('Frameworks : A new hope...', 'My second post'), $post->title_change());
        $this->assertEquals('Frameworks : A new hope...', $post->title_was());
    }
    
    public function test_date_attribute_changes()
    {
        $post = DirtyPost::$objects->get(1);
        $this->assertFalse($post->has_changed());
        $post->created_on = new SDateTime(2005,12,1,20,30);
        $this->assertFalse($post->has_changed());
        $post->created_on = new SDateTime(2006,12,1,20,30);
        $this->assertTrue($post->has_changed());
    }
}

