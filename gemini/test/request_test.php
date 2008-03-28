<?php

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->request = new SRequest();
    }
    
    public function test_accept_format()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/javascript';
        $this->assertEquals('js', $this->request->format());
    }
}

?>
