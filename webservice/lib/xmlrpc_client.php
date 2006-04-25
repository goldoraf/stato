<?php

class SXmlRpcClientException extends SException {}

class SXmlRpcClient
{
    private $uri = null;
    private $api = null;
    private $userAgent  = null;
    private $namespaces = array();
    
    public function __construct($uri, $api = null, $userAgent = 'Stato XML-RPC Client')
    {
        $this->uri = $uri;
        $this->api = $api;
        $this->userAgent = $userAgent;
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
        return $this->sendRequest($method, $args);
    }
    
    private function sendRequest($method, $args)
    {
        $request = new SXmlRpcRequest($method, $args);
        $headers = array
        (
            "Content-Type: text/xml",
            "User-Agent: {$this->userAgent}",
            "Content-length: ".$request->length()
        );
        $client = new SHttpClient($this->uri, $headers);
        $response = $client->post($request->toXml());
        
        if ($response->code != 200)
            throw new SXmlRpcClientException("Request failed with code {$response->code}");
        
        return $this->parseResponse($response->body);
    }
    
    private function parseResponse($xmlString)
    {
        try { $xml = new SimpleXMLElement($xmlString); }
        catch (Exception $e) { throw new SXmlRpcClientException('Failed to parse response'); }
        
        if (!empty($xml->fault))
        {
            if (empty($xml->fault->value))
                throw new SXmlRpcClientException('Invalid fault response : no <value> tag');
            
            try { $fault = SXmlRpcValue::typecast($xml->fault->value->asXML()); }
            catch (SXmlRpcValueException $e) { 
                throw new SXmlRpcClientException('Invalid fault response');
            }
            
            throw new SXmlRpcClientException('Request failed, '.$fault['faultCode']
                                             .': '.$fault['faultString']);
        }
        elseif (empty($xml->params))
            throw new SXmlRpcClientException('Invalid fault response : no <params> tag');
        elseif (empty($xml->params->param))
            throw new SXmlRpcClientException('Invalid fault response : no <param> tag');
        elseif (empty($xml->params->param->value))
            throw new SXmlRpcClientException('Invalid fault response : no <value> tag');
            
        return SXmlRpcValue::typecast($xml->params->param->value->asXML());
    }
}

class SXmlRpcValueException extends SException {}

class SXmlRpcValue
{
    private $value = null;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public static function typecast($xmlString)
    {
        try { $xml = new SimpleXMLElement($xmlString); }
        catch (Exception $e) { 
            throw new SXmlRpcValueException("Failed to typecast XML value : $xmlString");
        }
        
        list($type, $value) = each($xml);
        if (!$type) $type = 'string';
        
        switch ($type)
        {
            case 'i4':
            
            case 'int':
                return (integer) $value;
                break;
            case 'double':
                return (float) $value;
                break;
            case 'boolean':
                return $value == 1;
                break;
            case 'string':
                return $value;
                break;
            case 'dateTime.iso8601':
                return SDateTime::parse($value);
                break;
            case 'base64':
                
                break;
            case 'array':
                if (!$value instanceof SimpleXMLElement/* || empty($value->data)*/)
                    throw new SXmlRpcValueException('Invalid XML string for array type');
                
                $values = array();
                foreach ($value->data->value as $element)
                    $values[] = self::typecast($element->asXML());
                
                return $values;
                break;
            case 'struct':
                if (!$value instanceof SimpleXMLElement)
                    throw new SXmlRpcValueException('Invalid XML string for struct type');
                
                $values = array();
                foreach ($value->member as $member)
                {
                    if ((!$member->value instanceof SimpleXMLElement) || empty($member->value))
                        throw new SXmlRpcValueException('Member of a struct must contain a <value> tag');
                    
                    $values[(string) $member->name] = self::typecast($member->value->asXML());
                }
                return $values;
                break;
            default:
                throw new SXmlRpcValueException("$type is not a native XML-RPC type");
                break;
        }
    }
    
    public function toXml()
    {
        switch (gettype($this->value))
        {
            case 'boolean':
                return '<boolean>'.(($this->value) ? '1' : '0').'</boolean>';
                break;
            case 'integer':
                return '<int>'.$this->value.'</int>';
                break;
            case 'double':
                return '<double>'.$this->value.'</double>';
                break;
            case 'string':
                return '<string>'.htmlspecialchars($this->value).'</string>';
                break;
            case 'object':
                return $this->objectToXml($this->value);
                break;
            case 'array':
                return $this->arrayToXml($this->value);
                break;
        }
    }
    
    private function objectToXml($value)
    {
        switch (get_class($value))
        {
            case 'SDate':
                return '<dateTime.iso8601>'.$value->toIso8601().'</dateTime.iso8601>';
                break;
            case 'SDateTime':
                return '<dateTime.iso8601>'.$value->toIso8601().'</dateTime.iso8601>';
                break;
            case 'SBase64':
                
                break;
            default:
                return $this->arrayToXml(get_object_vars($value));
                break;
        }
    }
    
    private function arrayToXml($value)
    {
        if ($this->isStruct($value))
        {
            $xml = "<struct>\n";
            foreach ($this->data as $name => $value)
            {
                $v = new SXmlRpcValue($value);
                $xml.= "  <member><name>$name</name><value>";
                $xml.= $v->toXml()."</value></member>\n";
            }
            $xml.= '</struct>';
            return $xml;
        }
        else
        {
            $xml = "<array><data>\n";
            foreach ($this->data as $value)
            {
                $v = new SXmlRpcValue($value);
                $xml.= '  <value>'.$v->toXml()."</value>\n";
            }
            $xml.= '</data></array>';
            return $xml;
        }
    }
    
    private function isStruct($array)
    {
        $expected = 0;
        foreach ($array as $key => $value)
        {
            if ((string)$key != (string)$expected) return true;
            $expected++;
        }
        return false;
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
        . "<methodCall>\n<methodName>{$this->method}</methodName>\n<params>";
        foreach ($this->args as $arg)
        {
            $this->xml.= '<param><value>';
            $v = new SXmlRpcValue($arg);
            $this->xml.= $v->toXml();
            $this->xml.= "</value></param>\n";
        }
        $this->xml.= "</params>\n</methodCall>";
    }
    
    public function length()
    {
        return strlen($this->xml);
    }
    
    public function toXml()
    {
        return $this->xml;
    }
}

?>
