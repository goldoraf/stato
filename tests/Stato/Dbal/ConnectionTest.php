<?php

namespace Stato\Dbal;

use Stato\TestEnv;

use \PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../TestsHelper.php';

class ConnectionTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->connection = TestEnv::getDbConnection();
    }
    
    public function testGetConnection()
    {
        $this->assertThat(
            $this->connection->getPDOConnection(),
            $this->isInstanceOf('PDO')
        );
    }
    
    public function testExecute()
    {
        $this->assertThat(
            $this->connection->execute('SELECT 1'),
            $this->isInstanceOf('Stato\Dbal\ResultProxy')
        );
    }
    
    public function testResultIteration()
    {
        $res = $this->connection->execute('SELECT 1 AS testval');
        $values = array();
        foreach ($res as $row) $values[] = $row;
        $this->assertEquals(1, count($values));
        $this->assertEquals(array('testval' => 1), $values[0]);
    }
    
    public function testCreateAndReflectTable()
    {
        $table = new Table('test', array(
            new Column('id', Column::INTEGER, array('nullable' => false, 'primary_key' => true, 'auto_increment' => true)),
            new Column('lib', Column::STRING, array('length' => 50)),
            new Column('desc', Column::TEXT),
            new Column('flag', Column::STRING, array('length' => 3)),
            new Column('day', Column::DATE),
            new Column('created_on', Column::TIMESTAMP),
            //new Column('updated_on', Column::DATETIME),
            new Column('vat', Column::FLOAT),
        ));
        $this->connection->createTable($table);
        $this->assertTrue($this->connection->hasTable('test'));
        $this->assertEquals($table, $this->connection->reflectTable('test'));
        $this->connection->dropTable($table); // far from ideal : if the test fails, this table will not be dropped
    }
}
