<?php

class SXmlRpcServerException extends SException {}

class SXmlRpcServer
{
    public function __construct()
    {
    
    }
    
    public function parseRequest($xmlString)
    {
        try { $xml = new SimpleXMLElement($xmlString); }
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
                    
                $params[] = SXmlRpcValue::typecast($param->value->asXML());
            }
        }
        return array($method, $params);
    }
    
    public function writeResponse($value)
    {
        $value = new SXmlRpcValue($value);
        $xmlValue = $value->toXml();
        $xml = <<<EOD
<?xml version="1.0"?>
<methodResponse>
  <params>
    <param>
      <value>
        $xmlValue
      </value>
    </param>
  </params>
</methodResponse>

EOD;
        return $xml;
    }
}

?>
