<?php

namespace Stato\Webflow;

require_once __DIR__ . '/../TestsHelper.php';

class ResponseTest extends TestCase
{
    private $response;
    
    public function setUp()
    {
        $this->response = new Response();
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
