<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class BasicResourceWithReturns extends SResource
{
    public function get()
    {
        return array('key' => 'value');
    }
}

class BasicResourceWithoutReturns extends SResource
{
    public function get()
    {
        $this->responds(array('key' => 'value'));
    }
}

class ResourceTest extends StatoTestCase
{
    public function setup()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }
    
    public function test_basic_get()
    {
        $response = $this->dispatch(new BasicResourceWithReturns());
        $this->assertDomEquals('<result><key>value</key></result>', $response->body);
        $this->assertEquals(200, $response->headers['Status']);
        $response = $this->dispatch(new BasicResourceWithoutReturns());
        $this->assertDomEquals('<result><key>value</key></result>', $response->body);
        $this->assertEquals(200, $response->headers['Status']);
    }
    
    private function dispatch($resource, $method = 'get', $format = 'xml')
    {
        $request = new SRequest;
        $request->set_format($format);
        $request->inject_params(array('_method' => $method));
        return $resource->dispatch($request, new SResponse());
    }
}

