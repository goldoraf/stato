<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'connection.php';
require_once 'databases/mysql.php';
require_once 'expression.php';
require_once 'schema.php';

class Stato_ConnectionTest extends Stato_DatabaseTestCase
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
        $table = new Stato_Table('test', array(
            new Stato_Column('id', Stato_Column::INTEGER, array('length' => 11, 'nullable' => false, 'primary_key' => true, 'auto_increment' => true)),
            new Stato_Column('lib', Stato_Column::STRING, array('length' => 50)),
            new Stato_Column('desc', Stato_Column::TEXT),
            new Stato_Column('flag', Stato_Column::STRING, array('length' => 3)),
            new Stato_Column('day', Stato_Column::DATE),
            new Stato_Column('created_on', Stato_Column::DATETIME),
            //new Stato_Column('updated_on', Stato_Column::TIMESTAMP),
            new Stato_Column('vat', Stato_Column::FLOAT),
        ));
        $this->connection->createTable($table);
        $this->assertTrue($this->connection->hasTable('test'));
        $this->assertEquals($table, $this->connection->reflectTable('test'));
        $this->connection->dropTable($table); // far from ideal : if the test fails, this table will not be dropped
    }
}