<?php

define('STATO_APP_ROOT_PATH', STATO_CORE_PATH.'/build');

class UnkownError extends Exception {}

class RescueTest extends UnitTestCase
{
    private $request;
    
    public function setup()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->request = new SRequest();
    }
    
    public function test_404_in_public()
    {
        $this->request->set_format('html');
        $response = SRescue::in_public($this->request, new SRoutingException());
        $this->assertEqual(404, $response->status);
        $this->assertWantedPattern('|404 Page not found|', $response->body);
    }
    
    public function test_500_in_public()
    {
        $this->request->set_format('html');
        $response = SRescue::in_public($this->request, new UnkownError());
        $this->assertEqual(500, $response->status);
        $this->assertWantedPattern('|500 Internal Error|', $response->body);
    }
}

?>
