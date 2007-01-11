<?php

class ControllerTestCase extends UnitTestCase
{
    protected function process($controller, $action = 'show', $params = array())
    {
        $request = new MockRequest();
        $request->action = $action;
        $request->params = $params;
        $c = new $controller();
        return $c->process($request, new MockResponse());
    }
}

?>
