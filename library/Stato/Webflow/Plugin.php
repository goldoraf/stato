<?php




abstract class Stato_Webflow_Plugin 
{

    protected $response;
    protected $request;
    
    public function setResponse(Stato_Webflow_Response $response)
    {
        $this->response = $response;
        return $this;
    }	

    public function setRequest(Stato_Webflow_Request $request)
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
