<?php

class ControllerTestCase extends StatoTestCase
{
    protected $controller = null;
    protected $request    = null;
    protected $response   = null;
    protected $assigns    = null;
    protected $session    = array();
    protected $flash      = array();
    
    const SUCCESS  = '200 OK';
    const ERROR    = '500 Internal Error';
    const MISSING  = '404 Page Not Found';
    const REDIRECT = '302 Found';
    
    protected function process($controller, $action = 'show', $params = array())
    {
        $request = new MockRequest();
        $request->action = $action;
        $request->params = $params;
        $c = new $controller();
        return $c->process($request, new MockResponse());
    }
    
    protected function get($action, $params = array())
    {
        $this->ensure_setup_ok();
        $this->setup_and_process($action, $params);
    }
    
    protected function post($action, $params = array())
    {
        $this->ensure_setup_ok();
        $this->request->method = 'POST';
        $this->setup_and_process($action, $params);
    }
    
    protected function assertResponse($code, $message = '%s')
    {
        return $this->assertEqual($this->response->headers['Status'], $code, $message);
    }
    
    protected function assertRedirectedTo($params, $message = '%s')
    {
        return $this->assertTrue(
            $this->response->headers['Status'] == self::REDIRECT
            && $this->response->headers['location'] == SUrlRewriter::rewrite($params),
            $message
        );
    }
    
    protected function assertText($text, $message = '%s')
    {
        return $this->assert(new TextExpectation($text), $this->response->body, $message);
    }
    
    protected function assertNoText($text, $message = '%s')
    {
        return $this->assert(new NoTextExpectation($text), $this->response->body, $message);
    }
    
    // public because overrides UnitTestCase PHP4 method
    public function assertPattern($pattern, $message = '%s')
    {
        return $this->assert(new PatternExpectation($pattern), $this->response->body, $message);
    }
    
    // public because overrides UnitTestCase PHP4 method
    public function assertNoPattern($pattern, $message = '%s')
    {
        return $this->assert(new NoPatternExpectation($pattern), $this->response->body, $message);
    }
    
    private function setup_and_process($action, $params = array())
    {
        $this->request->action = $action;
        $this->request->params = $params;
        $this->request->relative_url_root = '/';
        
        try {
            $this->controller->process($this->request, $this->response);
        } catch (Exception $e) {
            SActionController::process_with_exception($this->request, $this->response, $e);
        }
        
        $this->assigns = $this->response->assigns;
        if (isset($this->assigns['session'])) $this->session = $this->assigns['session'];
        if (isset($this->assigns['flash']))   $this->flash = $this->assigns['flash'];
    }
    
    private function ensure_setup_ok()
    {
        if ($this->controller === null || $this->request === null || $this->response === null)
            throw new Exception('Controller, mock request or mock response was not correctly setup');
    }
}

?>
