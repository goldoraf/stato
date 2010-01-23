<?php

class FakeLogger
{
    public $logFile;
    
    public function __construct($logFile)
    {
        $this->logFile = $logFile;
    }
}