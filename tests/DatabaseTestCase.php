<?php

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

class Stato_DatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
    protected $connectionConfig;
    
    public function setup()
    {
        $this->connectionConfig = Stato_TestEnv::getDbDriverConfig();
        $this->connection = new Stato_Connection($this->connectionConfig);
        parent::setup();
    }
    
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection->getPDOConnection(), $this->connectionConfig['dbname']);
    }
    
    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_CsvDataSet();
    }
}