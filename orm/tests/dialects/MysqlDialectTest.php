<?php

require_once 'AbstractDialectTestCase.php';

require_once 'databases/mysql.php';

class Stato_MysqlDialectTest extends Stato_AbstractDialectTestCase
{
    public function getDialectName()
    {
        return 'mysql';
    }
    
    public function createTables()
    {
        $conn = $this->getPDOConnection();
        $conn->exec('CREATE TABLE IF NOT EXISTS `test1` (`foo` VARCHAR(255) NOT NULL)');
        $conn->exec('CREATE TABLE IF NOT EXISTS `test2` (`bar` VARCHAR(255) NOT NULL)');
        $conn->exec(
            'CREATE TABLE IF NOT EXISTS `test3` (
              `id` int(11) NOT NULL auto_increment,
              `lib` varchar(50) default NULL,
              `desc` text,
              `flag` char(3) default NULL,
              `day` date default NULL,
              `created_on` datetime default NULL,
              `updated_on` timestamp NULL default NULL,
              `vat` float default NULL,
              PRIMARY KEY  (`id`)
            )'
        );
    }
    
    public function dropTables()
    {
        $conn = $this->getPDOConnection();
        $conn->exec('DROP TABLE IF EXISTS `test1`');
        $conn->exec('DROP TABLE IF EXISTS `test2`');
        $conn->exec('DROP TABLE IF EXISTS `test3`');
    }
    
    public function testGetDsn()
    {
        $this->assertEquals('mysql:host=localhost;dbname=testdb',
            $this->getDialect()->getDsn(array('host' => 'localhost', 'dbname' => 'testdb')));  
        $this->assertEquals('mysql:host=localhost;port=3307;dbname=testdb',
            $this->getDialect()->getDsn(array('host' => 'localhost', 'dbname' => 'testdb', 'port' => 3307)));
        $this->assertEquals('mysql:unix_socket=/tmp/mysql.sock;dbname=testdb',
            $this->getDialect()->getDsn(array('unix_socket' => '/tmp/mysql.sock', 'dbname' => 'testdb')));
    }
}