<?php

namespace Stato\Orm;

use Exception;
use Stato\TestEnv;
use PHPUnit_Extensions_Database_TestCase;
use PHPUnit_Extensions_Database_DataSet_CsvDataSet;

require_once __DIR__ . '/../TestsHelper.php';

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

class TestCase extends PHPUnit_Extensions_Database_TestCase
{
    protected static $tables;
    
    protected $db;
    protected $connection;
    protected $fixtures = array();
    
    public function setup()
    {
        if (!isset(self::$tables)) self::$tables = include_once 'files/schema.php';
        $this->connection = TestEnv::getDbConnection();
        $this->db = new Database($this->connection);
        $this->createTestDatabase();
        parent::setup();
    }
    
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection->getPDOConnection(), $this->connection->getDatabase());
    }
    
    protected function createTestDatabase()
    {
        foreach (self::$tables as $table) {
            $this->connection->createTable($table);
            $this->db->addTable($table);
        }
    }
    
    protected function getDataSet()
    {
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        foreach ($this->fixtures as $fixture) {
            if (!array_key_exists($fixture, self::$tables))
                throw new Exception($fixture.' table is not defined in the test DB schema file');
            
            $dataSet->addTable($fixture, __DIR__ . '/fixtures/'.$fixture.'.csv');
        }
        return $dataSet;
    }
}
