<?php

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
    public function test_basic_get()
    {
        $response = $this->dispatch(new BasicResourceWithReturns());
        $this->assertDomEqual('<result><key>value</key></result>', $response->body);
        $this->assertEqual(200, $response->headers['Status']);
        $response = $this->dispatch(new BasicResourceWithoutReturns());
        $this->assertDomEqual('<result><key>value</key></result>', $response->body);
        $this->assertEqual(200, $response->headers['Status']);
    }
    
    private function dispatch($resource, $method = 'get', $format = 'xml')
    {
        $request = new SRequest;
        $request->set_format($format);
        $request->inject_params(array('_method' => $method));
        return $resource->dispatch($request, new SResponse());
    }
}

?>
