<?php

namespace Stato\Orm;

use Stato\TestEnv;

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

class TestCase extends \PHPUnit_Extensions_Database_TestCase
{
    protected static $tables;
    
    protected $db;
    protected $connection;
    protected $connectionConfig;
    protected $fixtures = array();
    
    public function setup()
    {
        if (!isset(self::$tables)) self::$tables = include_once 'files/schema.php';
        $this->connectionConfig = TestEnv::getDbDriverConfig();
        $this->connection = new Connection($this->connectionConfig);
        $this->db = new Database($this->connection);
        $this->createTestDatabase();
        parent::setup();
    }
    
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection->getPDOConnection(), $this->connectionConfig['dbname']);
    }
    
    protected function createTestDatabase()
    {
        $dbName = $this->connectionConfig['dbname'];
        $this->connection->execute("DROP DATABASE IF EXISTS {$dbName}");
        $this->connection->execute("CREATE DATABASE {$dbName}");
        $this->connection->execute("USE {$dbName}");
        
        foreach (self::$tables as $table) {
            $this->connection->createTable($table);
            $this->db->addTable($table);
        }
    }
    
    protected function getDataSet()
    {
        $dataSet = new \PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        foreach ($this->fixtures as $fixture) {
            if (!array_key_exists($fixture, self::$tables))
                throw new \Exception($fixture.' table is not defined in the test DB schema file');
            
            $dataSet->addTable($fixture, __DIR__ . '/fixtures/'.$fixture.'.csv');
        }
        return $dataSet;
    }
}
