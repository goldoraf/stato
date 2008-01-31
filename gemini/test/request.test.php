<?php

class RequestTest extends UnitTestCase
{
    public function setup()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->request = new SRequest();
    }
    
    public function test_accept_format()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/javascript';
        $this->assertEqual('js', $this->request->format());
    }
}

?>