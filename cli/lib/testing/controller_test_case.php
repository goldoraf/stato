<?php

class ControllerTestCase extends UnitTestCase
{
    protected function process($controller, $action = 'show')
    {
        $request = new MockRequest();
        $request->action = $action;
        $c = new $controller();
        return $c->process($request, new MockResponse());
    }
}

?>
