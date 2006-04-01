<?php

class ListDecoratorTest extends ActiveTestCase
{
    public $fixtures = array('topics', 'forums');
    public $useInstantiatedFixtures = True;
    
    function testReordering()
    {
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
        
        $topic = new SListDecorator($this->topics['list_2'], 'forum');
        $topic->moveLower();
        $this->instanciateFixtures(); // if not called, the topics array will not be refreshed and the next test will fail
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_2'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
        
        $topic = new SListDecorator($this->topics['list_2'], 'forum');
        $topic->moveHigher();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $topic = new SListDecorator($this->topics['list_1'], 'forum');
        $topic->moveToBottom();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4'],
                                 $this->topics['list_1']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $topic = new SListDecorator($this->topics['list_1'], 'forum');
        $topic->moveToTop();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $topic = new SListDecorator($this->topics['list_2'], 'forum');
        $topic->moveToBottom();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4'],
                                 $this->topics['list_2']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $topic = new SListDecorator($this->topics['list_4'], 'forum');
        $topic->moveToTop();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_4'],
                                 $this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_2']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $topic = new SListDecorator($this->topics['list_3'], 'forum');
        $topic->moveToBottom();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_4'],
                                 $this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
    }
    
    function testHigherLower()
    {
        $topic = new SListDecorator($this->topics['list_1'], 'forum');
        $this->assertEqual($this->topics['list_2'], $topic->lowerItem());
        $this->assertNull($topic->higherItem());
        $topic = new SListDecorator($this->topics['list_4'], 'forum');
        $this->assertEqual($this->topics['list_3'], $topic->higherItem());
        $this->assertNull($topic->lowerItem());
    }
    
    function testInsert()
    {
        $new = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new->save();
        $this->assertEqual(1, $new->position);
        $this->assertTrue($new->isFirst());
        $this->assertTrue($new->isLast());
        
        $new = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new->save();
        $this->assertEqual(2, $new->position);
        $this->assertFalse($new->isFirst());
        $this->assertTrue($new->isLast());
        
        $new = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new->save();
        $this->assertEqual(3, $new->position);
        $this->assertFalse($new->isFirst());
        $this->assertTrue($new->isLast());
        
        $new = new SListDecorator(new Topic(array('forum_id' => 3)), 'forum');
        $new->save();
        $this->assertEqual(1, $new->position);
        $this->assertTrue($new->isFirst());
        $this->assertTrue($new->isLast());
    }
    
    function testInsertAt()
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
        
        $new4->insertAt(3);
        $this->assertEqual(3, $new4->position);
        $new = new SListDecorator(SActiveStore::findByPk('Topic', $new->id), 'forum');
        $this->assertEqual(4, $new->position);
        
        $new->insertAt(2);
        $this->assertEqual(2, $new->position);
        $new4 = new SListDecorator(SActiveStore::findByPk('Topic', $new4->id), 'forum');
        $this->assertEqual(4, $new4->position);
        
        $new5 = new SListDecorator(new Topic(array('forum_id' => 2)), 'forum');
        $new5->save();
        $this->assertEqual(5, $new5->position);
        $new5->insertAt(1);
        $this->assertEqual(1, $new5->position);
        $new4 = new SListDecorator(SActiveStore::findByPk('Topic', $new4->id), 'forum');
        $this->assertEqual(5, $new4->position);
    }
    
    function testDelete()
    {
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $topic = new SListDecorator($this->topics['list_2'], 'forum');
        $topic->delete();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
        
        $this->assertEqual(1, $this->topics['list_1']->position);
        $this->assertEqual(2, $this->topics['list_3']->position);
        $this->assertEqual(3, $this->topics['list_4']->position);
        
        $topic = new SListDecorator($this->topics['list_1'], 'forum');
        $topic->delete();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
        
        $this->assertEqual(1, $this->topics['list_3']->position);
        $this->assertEqual(2, $this->topics['list_4']->position);
    }
}

?>
