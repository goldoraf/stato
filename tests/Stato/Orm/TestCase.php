<?php

namespace Stato\Orm;

use Stato\TestEnv;

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

class TestCase extends \PHPUnit_Extensions_Database_TestCase
{
    protected static $tables;
    
    protected $fixtures = array();
    protected $connectionConfig;
    protected $dbName;
    
    public function setup()
    {
        if (!isset(self::$tables)) self::$tables = include_once 'files/schema.php';
        
        $this->connectionConfig = TestEnv::getDbDriverConfig();
        $this->dbName = $this->connectionConfig['dbname'];
        $this->connection = new Connection($this->connectionConfig);
        $this->createTestDatabase();
        parent::setup();
    }
    
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection->getPDOConnection(), $this->connectionConfig['dbname']);
    }
    
    protected function createTestDatabase()
    {
        $this->connection->execute("DROP DATABASE IF EXISTS {$this->dbName}");
        $this->connection->execute("CREATE DATABASE {$this->dbName}");
        $this->connection->execute("USE {$this->dbName}");
        
        foreach ($this->fixtures as $fixture) {
            if (!array_key_exists($fixture, self::$tables))
                throw new \Exception($fixture.' table is not defined in the test DB schema file');
                
            $this->connection->createTable(self::$tables[$fixture]);
        }
    }
    
    protected function getDataSet()
    {
        $dataSet = new \PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        foreach ($this->fixtures as $fixture)
            $dataSet->addTable($fixture, __DIR__ . '/fixtures/'.$fixture.'.csv');
        return $dataSet;
    }
}