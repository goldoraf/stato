<?php

require_once dirname(__FILE__) . '/../../../tests/TestsHelper.php';

require_once 'connection.php';
require_once 'column.php';
require_once 'databases/mysql.php';

class Stato_MysqlDialectTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->dialect = new Stato_MysqlDialect();
    }
    
    public function testGetDsn()
    {
        $this->assertEquals('mysql:host=localhost;dbname=testdb',
            $this->dialect->getDsn(array('host' => 'localhost', 'dbname' => 'testdb')));  
        $this->assertEquals('mysql:host=localhost;port=3307;dbname=testdb',
            $this->dialect->getDsn(array('host' => 'localhost', 'dbname' => 'testdb', 'port' => 3307)));
        $this->assertEquals('mysql:unix_socket=/tmp/mysql.sock;dbname=testdb',
            $this->dialect->getDsn(array('unix_socket' => '/tmp/mysql.sock', 'dbname' => 'testdb')));
    }
    
    public function testGetColumnSpecification()
    {
        $this->assertEquals('`product` varchar(255)', $this->dialect->getColumnSpecification(
            new Stato_Column('product', Stato_Column::STRING)));
        $this->assertEquals('`product` varchar(50)', $this->dialect->getColumnSpecification(
            new Stato_Column('product', Stato_Column::STRING, array('length' => 50))));
        $this->assertEquals('`product` varchar(50) DEFAULT \'test\'', $this->dialect->getColumnSpecification(
            new Stato_Column('product', Stato_Column::STRING, array('length' => 50, 'default' => 'test'))));
        $this->assertEquals('`product` varchar(50) DEFAULT NULL', $this->dialect->getColumnSpecification(
            new Stato_Column('product', Stato_Column::STRING, array('length' => 50, 'default' => null))));
        $this->assertEquals('`product` varchar(50) NOT NULL', $this->dialect->getColumnSpecification(
            new Stato_Column('product', Stato_Column::STRING, array('length' => 50, 'nullable' => false))));
        $this->assertEquals('`flag` tinyint(1)', $this->dialect->getColumnSpecification(
            new Stato_Column('flag', Stato_Column::BOOLEAN)));
        $this->assertEquals('`flag` tinyint(1) DEFAULT 1', $this->dialect->getColumnSpecification(
            new Stato_Column('flag', Stato_Column::BOOLEAN, array('default' => true))));
        $this->assertEquals('`id` int(11) NOT NULL auto_increment', $this->dialect->getColumnSpecification(
            new Stato_Column('id', Stato_Column::INTEGER, array('nullable' => false, 'auto_increment' => true))));
    }
    
    public function testCreateTable()
    {
        $this->assertEquals('CREATE TABLE `foo` (`bar` varchar(255))',
            $this->dialect->createTable(new Stato_Table('foo', array(new Stato_Column('bar', Stato_Column::STRING)))));
    }
    
    public function testDropTable()
    {
        $this->assertEquals('DROP TABLE `foo`', $this->dialect->dropTable('foo'));
    }
    
    public function testAddColumn()
    {
        $this->assertEquals('ALTER TABLE `foo` ADD `bar` varchar(255)',
            $this->dialect->addColumn('foo', new Stato_Column('bar', Stato_Column::STRING)));
    }
}