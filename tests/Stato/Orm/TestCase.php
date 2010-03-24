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
    protected $db;
    protected $connection;
    protected $fixtures = array();
    
    public function setup()
    {
        $this->connection = TestEnv::getDbConnection();
        $this->db = new Database($this->connection);
        foreach (TestEnv::getDbSchema() as $table) {
            $this->db->addTable($table);
        }
        parent::setup();
    }
    
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection->getPDOConnection(), $this->connection->getDatabase());
    }
    
    protected function getDataSet()
    {
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        foreach ($this->fixtures as $fixture) {
            $dataSet->addTable($fixture, __DIR__ . '/fixtures/'.$fixture.'.csv');
        }
        return $dataSet;
    }
}
