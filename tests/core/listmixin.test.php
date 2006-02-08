<?php

class SListMixinTest extends ActiveTestCase
{
    public $fixtures = array('topics', 'forums');
    public $useInstantiatedFixtures = True;
    
    function testReordering()
    {
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SSActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
        
        $this->topics['list_2']->moveLower();
        $this->instanciateFixtures(); // if not called, the topics array will not be refreshed and the next test will fail
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_2'],
                                 $this->topics['list_4']),
                                 SSActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
        
        $this->topics['list_2']->moveHigher();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SSActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $this->topics['list_1']->moveToBottom();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4'],
                                 $this->topics['list_1']),
                                 SSActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $this->topics['list_1']->moveToTop();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SSActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $this->topics['list_2']->moveToBottom();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4'],
                                 $this->topics['list_2']),
                                 SSActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $this->topics['list_4']->moveToTop();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_4'],
                                 $this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_2']),
                                 SSActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $this->topics['list_3']->moveToBottom();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_4'],
                                 $this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3']),
                                 SSActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
    }
    
    function testHigherLower()
    {
        $this->assertEqual($this->topics['list_2'], $this->topics['list_1']->lowerItem());
        $this->assertNull($this->topics['list_1']->higherItem());
        $this->assertEqual($this->topics['list_3'], $this->topics['list_4']->higherItem());
        $this->assertNull($this->topics['list_4']->lowerItem());
    }
    
    function testInsert()
    {
        $new = SActiveStore::create('Topic', array('forum_id' => 2));
        $this->assertEqual(1, $new->position);
        $this->assertTrue($new->isFirst());
        $this->assertTrue($new->isLast());
        
        $new = SActiveStore::create('Topic', array('forum_id' => 2));
        $this->assertEqual(2, $new->position);
        $this->assertFalse($new->isFirst());
        $this->assertTrue($new->isLast());
        
        $new = SActiveStore::create('Topic', array('forum_id' => 2));
        $this->assertEqual(3, $new->position);
        $this->assertFalse($new->isFirst());
        $this->assertTrue($new->isLast());
        
        $new = SActiveStore::create('Topic', array('forum_id' => 3));
        $this->assertEqual(1, $new->position);
        $this->assertTrue($new->isFirst());
        $this->assertTrue($new->isLast());
    }
    
    function testInsertAt()
    {
        $new = SActiveStore::create('Topic', array('forum_id' => 2));
        $this->assertEqual(1, $new->position);
        $new = SActiveStore::create('Topic', array('forum_id' => 2));
        $this->assertEqual(2, $new->position);
        $new = SActiveStore::create('Topic', array('forum_id' => 2));
        $this->assertEqual(3, $new->position);
        $new4 = SActiveStore::create('Topic', array('forum_id' => 2));
        $this->assertEqual(4, $new4->position);
        
        $new4->insertAt(3);
        $this->assertEqual(3, $new4->position);
        $new = SActiveStore::findByPk('Topic', $new->id);
        $this->assertEqual(4, $new->position);
        
        $new->insertAt(2);
        $this->assertEqual(2, $new->position);
        $new4 = SActiveStore::findByPk('Topic', $new4->id);
        $this->assertEqual(4, $new4->position);
        
        $new5 = SActiveStore::create('Topic', array('forum_id' => 2));
        $this->assertEqual(5, $new5->position);
        $new5->insertAt(1);
        $this->assertEqual(1, $new5->position);
        $new4 = SActiveStore::findByPk('Topic', $new4->id);
        $this->assertEqual(5, $new4->position);
    }
    
    function testDelete()
    {
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_2'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
                                 
        $this->topics['list_2']->delete();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_1'],
                                 $this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
        
        $this->assertEqual(1, $this->topics['list_1']->position);
        $this->assertEqual(2, $this->topics['list_3']->position);
        $this->assertEqual(3, $this->topics['list_4']->position);
        
        $this->topics['list_1']->delete();
        $this->instanciateFixtures();
        
        $this->assertEqual(array($this->topics['list_3'],
                                 $this->topics['list_4']),
                                 SActiveStore::findAll('Topic', 'forum_id = 1', array('order' => 'position ASC')));
        
        $this->assertEqual(1, $this->topics['list_3']->position);
        $this->assertEqual(2, $this->topics['list_4']->position);
    }
}

?>
