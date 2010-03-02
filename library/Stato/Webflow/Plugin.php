<?php

namespace Stato\Webflow;


abstract class Plugin {

    protected $response;
    protected $request;
    
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }	

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }	

    public function preRouting()
    {}

    public function postRouting()
    {}

    public function preDispatch()
    {}

    public function postDispatch()
    {}

}
