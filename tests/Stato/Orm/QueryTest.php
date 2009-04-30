<?php

namespace Stato\Orm;

use User;

require_once __DIR__ . '/../TestsHelper.php';

require_once 'Stato/Orm/Schema.php';
require_once __DIR__ . '/files/user.php';

class QueryTest extends TestCase
{
    public function setup()
    {
        parent::setup();
        $this->query = new Query('User', $this->connection);
        $this->users = User::$table;
    }
    
    public function testFilter()
    {
        $users = $this->query->filter($this->users->fullname->like('%Doe'))->all();
        $this->assertEquals('John Doe', $users[0]->fullname);
        $this->assertEquals('Jane Doe', $users[1]->fullname);
    }
    
    public function testCloning()
    {
        $users1 = $this->query->filter($this->users->fullname->like('%Doe'));
        $users2 = $users1->filter($this->users->id->gt(1));
        $this->assertNotEquals($users1, $users2);
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
    
    protected function getDataSet()
    {
        $dataSet = new \PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        $dataSet->addTable('users', __DIR__ . '/fixtures/users.csv');
        return $dataSet;
    }
}