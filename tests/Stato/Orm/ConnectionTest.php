<?php

namespace Stato\Orm;

require_once __DIR__ . '/../TestsHelper.php';

class ConnectionTest extends TestCase
{
    public function testGetConnection()
    {
        $this->assertThat(
            $this->connection->getPDOConnection(),
            $this->isInstanceOf('PDO')
        );
    }
    
    public function testCreateAndReflectTable()
    {
        $table = new Table('test', array(
            new Column('id', Column::INTEGER, array('length' => 11, 'nullable' => false, 'primary_key' => true, 'auto_increment' => true)),
            new Column('lib', Column::STRING, array('length' => 50)),
            new Column('desc', Column::TEXT),
            new Column('flag', Column::STRING, array('length' => 3)),
            new Column('day', Column::DATE),
            new Column('created_on', Column::DATETIME),
            //new Column('updated_on', Column::TIMESTAMP),
            new Column('vat', Column::FLOAT),
        ));
        $this->connection->createTable($table);
        $this->assertTrue($this->connection->hasTable('test'));
        $this->assertEquals($table, $this->connection->reflectTable('test'));
        $this->connection->dropTable($table); // far from ideal : if the test fails, this table will not be dropped
    }
}