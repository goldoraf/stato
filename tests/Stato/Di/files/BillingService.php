<?php

class BillingService
{
    private $processor;
    private $logger;
    
    public function __construct($processor, $logger)
    {
        $this->processor = $processor;
        $this->logger = $logger;
    }
}