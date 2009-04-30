<?php

namespace Stato\Orm;

use Stato\TestEnv;

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

class TestCase extends \PHPUnit_Extensions_Database_TestCase
{
    protected $connectionConfig;
    
    public function setup()
    {
        $this->connectionConfig = TestEnv::getDbDriverConfig();
        $this->connection = new Connection($this->connectionConfig);
        parent::setup();
    }
    
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection->getPDOConnection(), $this->connectionConfig['dbname']);
    }
    
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_CsvDataSet();
    }
}