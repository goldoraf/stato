<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'response.php';

class Stato_ResponseTest extends PHPUnit_Framework_TestCase
{
    private $response;
    
    public function setUp()
    {
        $this->response = new Stato_Response();
    }
    
    public function tearDown()
    {
        unset($this->response);
    }
    
    public function testGetStatusAndHeaders()
    {
        $this->response->setHeader('Content-type', 'application/pdf');
        $this->assertEquals(200, $this->response->getStatus());
        $this->assertEquals(array('Content-type' => 'application/pdf'),
                            $this->response->getHeaders());
    }
    
    public function testSend()
    {
        $this->response->setBody('hello world');
        ob_start();
        $this->response->send();
        $this->assertEquals('hello world', ob_get_clean());
    }
}
