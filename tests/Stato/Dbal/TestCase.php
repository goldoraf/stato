<?php

namespace Stato\Dbal;

use Stato\TestEnv;

use \PHPUnit_Extensions_Database_TestCase;
use \PHPUnit_Extensions_Database_DataSet_CsvDataSet;

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
        TestEnv::createTestDatabase();
        $this->connection = TestEnv::getDbConnection();
        parent::setup();
    }

    public function tearDown()
    {
        $this->connection->close();
        parent::tearDown();
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
