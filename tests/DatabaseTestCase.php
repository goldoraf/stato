<?php

class Stato_DatabaseTestCase extends PHPUnit_Framework_TestCase
{
    protected $connectionConfig;
    
    public function setup()
    {
        $this->connectionConfig = Stato_TestEnv::getDbDriverConfig();
        $this->connection = new Stato_Connection($this->connectionConfig);
    }
}