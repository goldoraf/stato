<?php

require_once dirname(__FILE__) . '/../../../tests/TestsHelper.php';

require_once 'db_engine.php';
require_once 'column.php';
require_once 'table.php';

abstract class Stato_AbstractDialectTestCase extends PHPUnit_Framework_TestCase
{
    abstract public function getDialectName();
    
    abstract public function createTables();
    
    abstract public function dropTables();
    
    abstract public function testGetDsn();
    
    public function __construct()
    {
        $config = include dirname(__FILE__) . '/../Config.php';
        $class = 'Stato_'.$this->getDialectName().'Dialect';
        $this->dialect = new $class();
        $this->config = $config[$this->getDialectName()];
        $this->connection = new PDO($this->config['dsn'], $this->config['user'], $this->config['password']);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function setup()
    {
        $this->createTables();
    }
    
    public function tearDown()
    {
        $this->dropTables();
    }
    
    public function getDialect()
    {
        return $this->dialect;
    }
    
    public function getConfig()
    {
        return $this->config;
    }
    
    public function getPDOConnection()
    {
        return $this->connection;
    }
    
    public function testGetConnection()
    {
        $this->assertThat(
            $this->dialect->getConnection($this->config),
            $this->isInstanceOf('PDO')
        );
    }
    
    public function testGetTableNamesAndHasTable()
    {
        $this->assertEquals(array('test1', 'test2', 'test3'), $this->dialect->getTableNames($this->connection));
        $this->assertTrue($this->dialect->hasTable($this->connection, 'test1'));
        $this->assertTrue($this->dialect->hasTable($this->connection, 'test2'));
        $this->assertTrue($this->dialect->hasTable($this->connection, 'test3'));
        $this->assertFalse($this->dialect->hasTable($this->connection, 'test4'));
    }
    
    public function testReflectTable()
    {
        $this->assertEquals(
            new Stato_Table('test3', array(
                new Stato_Column('id', Stato_Column::INTEGER, array('length' => 11, 'nullable' => false, 'primary_key' => true, 'auto_increment' => true)),
                new Stato_Column('lib', Stato_Column::STRING, array('length' => 50)),
                new Stato_Column('desc', Stato_Column::TEXT),
                new Stato_Column('flag', Stato_Column::STRING, array('length' => 3)),
                new Stato_Column('day', Stato_Column::DATE),
                new Stato_Column('created_on', Stato_Column::DATETIME),
                new Stato_Column('updated_on', Stato_Column::TIMESTAMP),
                new Stato_Column('vat', Stato_Column::FLOAT),
            )),
            $this->dialect->reflectTable($this->connection, 'test3')
        );
    }
}