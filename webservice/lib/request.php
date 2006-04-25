<?php

class SWebServiceRequest
{
    public $protocol = null;
    public $service  = null;
    public $method   = null;
    public $params   = null;
    
    public function __construct($protocol, $service, $method, $params)
    {
        $this->protocol = $protocol;
        $this->service  = $service;
        $this->method   = $method;
        $this->params   = $params;
    }
}

?>
