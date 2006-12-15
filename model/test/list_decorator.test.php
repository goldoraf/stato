<?php

class ListDecoratorTest extends ActiveTestCase
{
    public $fixtures = array('topics', 'forums');
    public $use_instantiated_fixtures = True;
    
    function test_reordering()
    {
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
        
        $topic = new SListDecorator($this->topics['list_2'], 'forum');
        $topic->move_lower();
        $this->instanciate_fixtures(); // if not called, the topics array will not be refreshed and the next test will fail
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_2'],
                                 $this->topics['list_4']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
        
        $topic = new SListDecorator($this->topics['list_2'], 'forum');
        $topic->move_higher();
        $this->instanciate_fixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
                                 
        $topic = new SListDecorator($this->topics['list_1'], 'forum');
        $topic->move_to_bottom();
        $this->instanciate_fixtures();
        
        $this->assertEqual(array($this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4'],
                                 $this->topics['list_1']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
                                 
        $topic = new SListDecorator($this->topics['list_1'], 'forum');
        $topic->move_to_top();
        $this->instanciate_fixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
                                 
        $topic = new SListDecorator($this->topics['list_2'], 'forum');
        $topic->move_to_bottom();
        $this->instanciate_fixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4'],
                                 $this->topics['list_2']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
                                 
        $topic = new SListDecorator($this->topics['list_4'], 'forum');
        $topic->move_to_top();
        $this->instanciate_fixtures();
        
        $this->assertEqual(array($this->topics['list_4'],
                                 $this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_2']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
                                 
        $topic = new SListDecorator($this->topics['list_3'], 'forum');
        $topic->move_to_bottom();
        $this->instanciate_fixtures();
        
        $this->assertEqual(array($this->topics['list_4'],
                                 $this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
    }
    
    function test_higher_lower()
    {
        $topic = new SListDecorator($this->topics['list_1'], 'forum');
        $this->assertEqual($this->topics['list_2'], $topic->lower_item());
        $this->assertNull($topic->higher_item());
        $topic = new SListDecorator($this->topics['list_4'], 'forum');
        $this->assertEqual($this->topics['list_3'], $topic->higher_item());
        $this->assertNull($topic->lower_item());
    }
    
    function test_insert()
    {
        $new = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new->save();
        $this->assertEqual(1, $new->position);
        $this->assertTrue($new->is_first());
        $this->assertTrue($new->is_last());
        
        $new = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new->save();
        $this->assertEqual(2, $new->position);
        $this->assertFalse($new->is_first());
        $this->assertTrue($new->is_last());
        
        $new = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new->save();
        $this->assertEqual(3, $new->position);
        $this->assertFalse($new->is_first());
        $this->assertTrue($new->is_last());
        
        $new = new SListDecorator(new Topic(array('forum_id' => 3)), 'forum');
        $new->save();
        $this->assertEqual(1, $new->position);
        $this->assertTrue($new->is_first());
        $this->assertTrue($new->is_last());
    }
    
    function test_insert_at()
    {
        $new = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new->save();
        $this->assertEqual(1, $new->position);
        $new = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new->save();
        $this->assertEqual(2, $new->position);
        $new = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new->save();
        $this->assertEqual(3, $new->position);
        $new4 = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new4->save();
        $this->assertEqual(4, $new4->position);
        
        $new4->insert_at(3);
        $this->assertEqual(3, $new4->position);
        $new = new SListDecorator(Topic::$objects->get($new->id), 'forum');
        $this->assertEqual(4, $new->position);
        
        $new->insert_at(2);
        $this->assertEqual(2, $new->position);
        $new4 = new SListDecorator(Topic::$objects->get($new4->id), 'forum');
        $this->assertEqual(4, $new4->position);
        
        $new5 = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new5->save();
        $this->assertEqual(5, $new5->position);
        $new5->insert_at(1);
        $this->assertEqual(1, $new5->position);
        $new4 = new SListDecorator(Topic::$objects->get($new4->id), 'forum');
        $this->assertEqual(5, $new4->position);
    }
    
    function test_delete()
    {
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
                                 
        $topic = new SListDecorator($this->topics['list_2'], 'forum');
        $topic->delete();
        $this->instanciate_fixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
        
        $this->assertEqual(1, $this->topics['list_1']->position);
        $this->assertEqual(2, $this->topics['list_3']->position);
        $this->assertEqual(3, $this->topics['list_4']->position);
        
        $topic = new SListDecorator($this->topics['list_1'], 'forum');
        $topic->delete();
        $this->instanciate_fixtures();
        
        $this->assertEqual(array($this->topics['list_3'],
                                 $this->topics['list_4']),
                                 Topic::$objects->filter('forum_id = 1')->order_by('position')->to_array());
        
        $this->assertEqual(1, $this->topics['list_3']->position);
        $this->assertEqual(2, $this->topics['list_4']->position);
    }
}

?>
