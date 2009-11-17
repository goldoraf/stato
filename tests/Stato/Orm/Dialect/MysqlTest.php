<?php

namespace Stato\Orm\Dialect;

use Stato\Orm\TestCase;
use Stato\Orm\Column;
use Stato\Orm\Table;

require_once __DIR__ . '/../../TestsHelper.php';

require_once 'Stato/Orm/Schema.php';

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->dialect = new Mysql();
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
            new Column('product', Column::STRING)));
        $this->assertEquals('`product` varchar(50)', $this->dialect->getColumnSpecification(
            new Column('product', Column::STRING, array('length' => 50))));
        $this->assertEquals('`product` varchar(50) DEFAULT \'test\'', $this->dialect->getColumnSpecification(
            new Column('product', Column::STRING, array('length' => 50, 'default' => 'test'))));
        $this->assertEquals('`product` varchar(50) DEFAULT NULL', $this->dialect->getColumnSpecification(
            new Column('product', Column::STRING, array('length' => 50, 'default' => null))));
        $this->assertEquals('`product` varchar(50) NOT NULL', $this->dialect->getColumnSpecification(
            new Column('product', Column::STRING, array('length' => 50, 'nullable' => false))));
        $this->assertEquals('`flag` tinyint(1)', $this->dialect->getColumnSpecification(
            new Column('flag', Column::BOOLEAN)));
        $this->assertEquals('`flag` tinyint(1) DEFAULT 1', $this->dialect->getColumnSpecification(
            new Column('flag', Column::BOOLEAN, array('default' => true))));
        $this->assertEquals('`id` int(11) NOT NULL auto_increment', $this->dialect->getColumnSpecification(
            new Column('id', Column::INTEGER, array('nullable' => false, 'auto_increment' => true))));
    }
    
    public function testCreateTable()
    {
        $this->assertEquals('CREATE TABLE `foo` (`bar` varchar(255))',
            $this->dialect->createTable(new Table('foo', array(new Column('bar', Column::STRING)))));
    }
    
    public function testDropTable()
    {
        $this->assertEquals('DROP TABLE `foo`', $this->dialect->dropTable('foo'));
    }
    
    public function testAddColumn()
    {
        $this->assertEquals('ALTER TABLE `foo` ADD `bar` varchar(255)',
            $this->dialect->addColumn('foo', new Column('bar', Column::STRING)));
    }
}