<?php

class SXmlRpcClientException extends SException {}
class SXmlRpcRequestFailedException extends SException {}

class SXmlRpcClient
{
    private $uri = null;
    private $api = null;
    private $user_agent  = null;
    private $namespaces = array();
    
    private $credentials = null;
    
    public function __construct($uri, $api = null, $user_agent = 'Stato XML-RPC Client')
    {
        $this->uri = $uri;
        $this->api = $api;
        $this->user_agent = $user_agent;
    }
    
    public function __get($namespace)
    {
        $this->namespaces[] = $namespace;
        return $this;
    }
    
    public function __call($method, $args)
    {
        if (!empty($this->namespaces))
        {
            $method = implode('.', $this->namespaces).".$method";
            $this->namespaces = array();
        }
        return $this->decode_response($this->send_request($method, $args));
    }
    
    public function set_http_credentials($username, $password)
    {
        $this->credentials = "$username:$password";
    }
    
    public function decode_response($xml_string)
    {
        try { $xml = new SimpleXMLElement($xml_string); }
        catch (Exception $e) { throw new SXmlRpcClientException('Failed to parse response'); }
        
        if (!empty($xml->fault))
        {
            if (empty($xml->fault->value))
                throw new SXmlRpcClientException('Invalid fault response : no <value> tag');
            
            try { $fault = SXmlRpcValue::typecast($xml->fault->value->asXml())->to_php(); }
            catch (SXmlRpcValueException $e) { 
                throw new SXmlRpcClientException('Invalid fault response');
            }
            
            throw new SXmlRpcRequestFailedException('Request failed, '.$fault['faultCode']
                                                    .': '.$fault['faultString']);
        }
        elseif (empty($xml->params))
            throw new SXmlRpcClientException('Invalid response : no <params> tag');
        elseif (empty($xml->params->param))
            throw new SXmlRpcClientException('Invalid response : no <param> tag');
        elseif (empty($xml->params->param->value))
            throw new SXmlRpcClientException('Invalid response : no <value> tag');
            
        return SXmlRpcValue::typecast($xml->params->param->value->asXml())->to_php();
    }
    
    public function send_request($method, $args)
    {
        $request = new SXmlRpcRequest($method, $args);
        
        $headers = array
        (
            "Content-Type: text/xml",
            "User-Agent: {$this->user_agent}",
            "Content-length: ".$request->length()
        );
        $client = new SHttpClient($this->uri, $headers, $this->credentials);
        $response = $client->post($request->to_xml());
        
        if ($response->code != 200)
            throw new SXmlRpcRequestFailedException("Request failed with code {$response->code}");
        
        return $response->body;
    }
}

class SXmlRpcRequest
{
    private $method = null;
    private $args   = array();
    private $xml    = '';
    
    public function __construct($method, $args)
    {
        $this->method = $method;
        $this->args   = $args;
        $this->xml    = '<?xml version="1.0"?>'."\n"
        . "<methodCall>\n  <methodName>{$this->method}</methodName>\n  <params>\n";
        foreach ($this->args as $arg)
        {
            $this->xml.= '    <param><value>';
            $v = new SXmlRpcValue($arg);
            $this->xml.= $v->to_xml();
            $this->xml.= "</value></param>\n";
        }
        $this->xml.= "  </params>\n</methodCall>";
    }
    
    public function length()
    {
        return strlen($this->xml);
    }
    
    public function to_xml()
    {
        return $this->xml;
    }
}

?>
