<?php

define('STATO_APP_ROOT_PATH', STATO_CORE_PATH.'/gemini/lib/templates/createapp');

class UnkownError extends Exception {}

class RescueTest extends UnitTestCase
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
        $this->assertEqual(404, $response->status);
        $this->assertPattern('|404 Page not found|', $response->body);
    }
    
    public function test_500_in_public()
    {
        $this->request->set_format('html');
        $response = SRescue::response($this->request, new SResponse(), new UnkownError());
        $this->assertEqual(500, $response->status);
        $this->assertPattern('|500 Internal Error|', $response->body);
    }
}

?>
