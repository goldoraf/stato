<?php

namespace Stato\Orm;

use TestUser;

use User;

require_once __DIR__ . '/../TestsHelper.php';

require_once 'Stato/Orm/Schema.php';
require_once __DIR__ . '/files/user.php';

class QueryTest extends TestCase
{
    protected $fixtures = array('users');
    
    public function setup()
    {
        parent::setup();
        $this->users = self::$tables['users'];
        $this->mapper = new Mapper('User', $this->users);
        $this->query = new Query($this->mapper, $this->connection);
    }
    
    public function testFilter()
    {
        $users = $this->query->filter($this->users->fullname->like('%Doe'))->all();
        $this->assertEquals('John Doe', $users[0]->fullname);
        $this->assertEquals('Jane Doe', $users[1]->fullname);
    }
    
    public function testFilterWithClosure()
    {
        $users = $this->query->filter(function($u) {
            return $u->fullname->like('%Doe');
        });
        $users = $users->all();
        $this->assertEquals('John Doe', $users[0]->fullname);
        $this->assertEquals('Jane Doe', $users[1]->fullname);
    }
    
    public function testFilterBy()
    {
        $users = $this->query->filterBy(array('login' => 'jdoe', 'password' => 'john'))->all();
        $this->assertEquals(1, count($users));
        $this->assertEquals('John Doe', $users[0]->fullname);
    }
    
    public function testFilterByWithArrayValue()
    {
        $users = $this->query->filterBy(array('id' => array(1,2)))->all();
        $this->assertEquals(2, count($users));
    }
    
    public function testCloning()
    {
        $users1 = $this->query->filter($this->users->fullname->like('%Doe'));
        $users2 = $users1->filter($this->users->id->gt(1));
        $this->assertNotEquals($users1, $users2);
    }
    
    public function testLimit()
    {
        $users = $this->query->filter($this->users->fullname->like('%Doe'))->limit(1)->all();
        $this->assertEquals(1, count($users));
        $this->assertEquals('John Doe', $users[0]->fullname);
    }
    
    public function testOrderBy()
    {
        $users = $this->query->orderBy($this->users->id->desc())->all();
        $this->assertEquals('John Doe', $users[1]->fullname);
        $this->assertEquals('Jane Doe', $users[0]->fullname);
    }
    
    public function testOrderByString()
    {
        $users = $this->query->orderBy('-id')->all();
        $this->assertEquals('John Doe', $users[1]->fullname);
        $this->assertEquals('Jane Doe', $users[0]->fullname);
    }
    
    public function testGet()
    {
        $user = $this->query->get(1);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('John Doe', $user->fullname);
    }
    
    public function testRecordNotFound()
    {
        $this->setExpectedException('Stato\Orm\RecordNotFound');
        $user = $this->query->get(99);
    }
    
    public function testInBulk()
    {
        $users = $this->query->inBulk(array(1,2));
        $this->assertEquals('John Doe', $users[1]->fullname);
        $this->assertEquals('Jane Doe', $users[2]->fullname);
    }
}