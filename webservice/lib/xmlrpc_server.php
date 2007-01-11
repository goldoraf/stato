<?php

class SXmlRpcServerException extends SException {}

class SXmlRpcServer
{
    public function __construct()
    {
    
    }
    
    public function parse_request($xml_string)
    {
        try { $xml = new SimpleXMLElement($xml_string); }
        catch (Exception $e) { throw new SXmlRpcServerException('Failed to parse request'); }
        
        if (empty($xml->methodName))
            throw new SXmlRpcServerException('No method name provided');
            
        $method = $xml->methodName;
        $params = array();
        
        if (!empty($xml->params))
        {
            foreach ($xml->params->param as $param)
            {
                if (!$param instanceof SimpleXMLElement)
                    throw new SXmlRpcServerException('Invalid request parameter');
                if (empty($param->value))
                    throw new SXmlRpcServerException('Invalid request parameter : no <value> tag');
                    
                $params[] = SXmlRpcValue::typecast($param->value->asXml());
            }
        }
        
        $parts = explode('.', $method);
        if (count($parts) < 2 || count($parts) > 3)
            throw new SXmlRpcServerException("Requested method does not exist : $method");
        
        $method = array_pop($parts);
        if (count($parts) == 2) $service = $parts[0].'/'.$parts[1];
        else $service = $parts[0];
        
        return new SWebServiceRequest('xmlrpc', $service, $method, $params);
    }
    
    public function write_response($value)
    {
        $value = new SXmlRpcValue($value);
        $xml_value = $value->to_xml();
        $xml = <<<EOD
<?xml version="1.0"?>
<methodResponse>
  <params>
    <param>
      <value>
        $xml_value
      </value>
    </param>
  </params>
</methodResponse>

EOD;
        return $xml;
    }
}

?>
