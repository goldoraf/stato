<?php

class SWebService
{
    public $request = null;
    public $params  = null;
    
    protected $api  = null;
    
    public function __construct()
    {
        // require api
    }
    
    public function invoke($request)
    {
        $this->request = $request;
        $this->params  =& $this->request->params;
        
        $method = $this->request->method;
        
        $this->before_invocation();
        $response = $this->$method();
        $this->after_invocation();
        
        return $response;
    }
    
    protected function before_invocation() {}
    
    protected function after_invocation() {}
}

?>
