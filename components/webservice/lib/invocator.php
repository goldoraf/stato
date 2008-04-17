<?php

class SWebServiceInvocator
{
    private $web_services = array();
    private $request;
    private $logger;
    
    public function __construct($request)
    {
        $this->request = $request;
        $this->logger  = SLogger::get_instance();
    }
    
    public function add_web_service($name, $instance)
    {
        $this->web_services[$name] = $instance;
    }
    
    public function invoke($protocol)
    {
        if (!in_array($protocol, array('xmlrpc')))
            throw new SUnknownProtocolException($protocol);
            
        $class = 'S'.$protocol.'Server';
        $server = new $class();
        
        try {
            $ws_request = $server->parse_request($this->request->raw_post_data());
        
            if (!array_key_exists($ws_request->service, $this->web_services))
                throw new SUnknownServiceException();
            
            $return_value = $this->web_services[$ws_request->service]->invoke($ws_request);
            $raw_response = $server->write_response($return_value);
        }
        catch (SWebServiceFault $fault) {
            $raw_response = $server->write_fault($fault->getMessage(), $fault->getCode());
        }
        catch (Exception $e) {
            $raw_response = $server->write_fault('Internal server error', 500);
            $this->logger->log_error($e);
        }
        
        return $raw_response;
    }
}

?>
