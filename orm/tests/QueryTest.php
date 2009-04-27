<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'connection.php';
require_once 'databases/mysql.php';
require_once 'expression.php';
require_once 'schema.php';
require_once 'compiler.php';
require_once 'helpers.php';
require_once 'mapper.php';
require_once 'query.php';

require_once dirname(__FILE__) . '/files/user.php';

class Stato_QueryTest extends Stato_DatabaseTestCase
{
    public function setup()
    {
        parent::setup();
        $this->query = new Stato_Query('User', $this->connection);
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
        $this->setExpectedException('Stato_RecordNotFound');
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
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        $dataSet->addTable('users', dirname(__FILE__) . '/fixtures/users.csv');
        return $dataSet;
    }
}