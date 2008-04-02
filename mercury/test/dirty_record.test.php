<?php

class DirtyRecordTest extends ActiveTestCase
{
    public $fixtures = array('posts');
    public $models = array('dirty_post');
    
    public function test_attribute_changes()
    {
        $post = new DirtyPost();
        $this->assertFalse($post->has_changed());
        $this->assertEqual(array(), $post->changed());
        $this->assertEqual(array(), $post->changes());
        $this->assertFalse($post->title_has_changed());
        $this->assertNull($post->title_change());
        $this->assertNull($post->title_was());
        
        $post->title = 'My first post';
        $this->assertTrue($post->has_changed());
        $this->assertEqual(array('title'), $post->changed());
        $this->assertEqual(array('title' => array(null, 'My first post')), $post->changes());
        $this->assertTrue($post->title_has_changed());
        $this->assertEqual(array(null, 'My first post'), $post->title_change());
        $this->assertNull($post->title_was());
        
        $post->save();
        $this->assertFalse($post->has_changed());
        $this->assertEqual(array(), $post->changed());
        $this->assertEqual(array(), $post->changes());
        $this->assertFalse($post->title_has_changed());
        $this->assertNull($post->title_change());
        $this->assertEqual('My first post', $post->title_was());
        
        $post->title = 'My first post';
        $this->assertFalse($post->has_changed());
        $this->assertEqual(array(), $post->changed());
        $this->assertEqual(array(), $post->changes());
        $this->assertFalse($post->title_has_changed());
        $this->assertNull($post->title_change());
        $this->assertEqual('My first post', $post->title_was());
        
        $post->title = 'My second post';
        $this->assertTrue($post->has_changed());
        $this->assertEqual(array('title'), $post->changed());
        $this->assertEqual(array('title' => array('My first post', 'My second post')), $post->changes());
        $this->assertTrue($post->title_has_changed());
        $this->assertEqual(array('My first post', 'My second post'), $post->title_change());
        $this->assertEqual('My first post', $post->title_was());
    }
    
    public function test_attribute_changes_after_loading()
    {
        $post = DirtyPost::$objects->get(1);
        $this->assertFalse($post->has_changed());
        $this->assertEqual(array(), $post->changed());
        $this->assertEqual(array(), $post->changes());
        $this->assertFalse($post->title_has_changed());
        $this->assertNull($post->title_change());
        $this->assertEqual('Frameworks : A new hope...', $post->title_was());
        
        $post->title = 'My second post';
        $this->assertTrue($post->has_changed());
        $this->assertEqual(array('title'), $post->changed());
        $this->assertEqual(array('title' => array('Frameworks : A new hope...', 'My second post')), $post->changes());
        $this->assertTrue($post->title_has_changed());
        $this->assertEqual(array('Frameworks : A new hope...', 'My second post'), $post->title_change());
        $this->assertEqual('Frameworks : A new hope...', $post->title_was());
    }
}

?>
