<?php

namespace Stato\Orm;

use Stato\TestEnv;

require_once __DIR__ . '/../TestsHelper.php';

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->connection = new Connection(TestEnv::getDbDriverConfig());
        $this->database = new Database($this->connection);
        $this->usersTable = new Table('users', array(
            new Column('id', Column::INTEGER, array('primary_key' => true, 'auto_increment' => true)),
            new Column('fullname', Column::STRING),
            new Column('login', Column::STRING),
            new Column('password', Column::STRING),
        ));
    }
    
    public function testGetConnection()
    {
        $this->assertThat(
            $this->database->getConnection(),
            $this->isInstanceOf('\Stato\Orm\Connection')
        );
    }
    
    public function testUnboundConnection()
    {
        $this->setExpectedException('\Stato\Orm\UnboundConnectionError');
        $db = new Database();
        $db->getConnection();
    }
    
    public function testConnect()
    {
        $db = new Database();
        $db->connect(TestEnv::getDbDriverConfig());
        $this->assertThat(
            $db->getConnection(),
            $this->isInstanceOf('\Stato\Orm\Connection')
        );
    }
    
    public function testAddTable()
    {
        $this->database->addTable($this->usersTable);
        $this->assertEquals($this->usersTable, $this->database->getTable('users'));
    }
    
    public function testFrom()
    {
        $this->database->addTable($this->usersTable);
        $this->assertThat(
            $this->database->from('users'),
            $this->isInstanceOf('\Stato\Orm\Dataset')
        );
        $this->assertThat(
            $this->database['users'],
            $this->isInstanceOf('\Stato\Orm\Dataset')
        );
    }
}
