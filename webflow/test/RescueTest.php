<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class UnkownError extends Exception {}

class RescueTest extends PHPUnit_Framework_TestCase
{
    private $request;
    
    public function setup()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        SActionController::$consider_all_requests_local = false;
        $this->request = new SRequest();
    }
    
    public function test_404_in_public()
    {
        $this->request->set_format('html');
        $response = SRescue::response($this->request, new SResponse(), new SRoutingException());
        $this->assertEquals(404, $response->status);
        $this->assertRegexp('|404 Page not found|', $response->body);
    }
    
    public function test_500_in_public()
    {
        $this->request->set_format('html');
        $response = SRescue::response($this->request, new SResponse(), new UnkownError());
        $this->assertEquals(500, $response->status);
        $this->assertRegexp('|500 Internal Error|', $response->body);
    }
}

