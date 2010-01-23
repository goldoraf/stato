<?php



require_once dirname(__FILE__) . '/../TestsHelper.php';

class Stato_Webflow_ResponseTest extends Stato_Webflow_TestCase
{
    private $response;
    
    public function setUp()
    {
        $this->response = new Stato_Webflow_Response();
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
