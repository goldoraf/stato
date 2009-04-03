<?php

class ControllerTestCase extends SimpleTestCase
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
    
    protected function get($action, $params = array())
    {
        $this->ensure_setup_ok();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setup_and_process($action, $params);
    }
    
    protected function post($action, $params = array())
    {
        $this->ensure_setup_ok();
        $_SERVER['REQUEST_METHOD'] = 'POST';
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
            && $this->response->headers['location'] == SUrlRewriter::url_for($params),
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
        $this->request = new SRequest();
        $params['action'] = $action;
        $this->request->inject_params($params);
        //$this->request->relative_url_root = '/';
        
        try {
            $this->response = $this->controller->dispatch($this->request);
        } catch (Exception $e) {
            
        }
        
        $this->assigns = $this->response->assigns;
        if (isset($this->assigns['session'])) $this->session = $this->assigns['session'];
        if (isset($this->assigns['flash']))   $this->flash = $this->assigns['flash'];
    }
    
    private function ensure_setup_ok()
    {
        if ($this->controller === null)
            throw new Exception('Controller was not correctly setup');
    }
}

?>
