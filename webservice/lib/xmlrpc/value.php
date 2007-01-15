<?php

class SXmlRpcValueException extends SException {}

class SXmlRpcValue
{
    private $value = null;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public static function typecast($xml_string)
    {
        try { $xml = new SimpleXMLElement($xml_string); }
        catch (Exception $e) { 
            throw new SXmlRpcValueException("Failed to typecast XML value : $xml_string");
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
                return base64_decode($value);
                break;
            case 'array':
                if (!$value instanceof SimpleXMLElement/* || empty($value->data)*/)
                    throw new SXmlRpcValueException('Invalid XML string for array type');
                
                $values = array();
                foreach ($value->data->value as $element)
                    $values[] = self::typecast($element->asXml());
                
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
                    
                    $values[(string) $member->name] = self::typecast($member->value->asXml());
                }
                return $values;
                break;
            default:
                throw new SXmlRpcValueException("$type is not a native XML-RPC type");
                break;
        }
    }
    
    public function to_xml()
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
                return $this->object_to_xml($this->value);
                break;
            case 'array':
                return $this->array_to_xml($this->value);
                break;
        }
    }
    
    private function object_to_xml($value)
    {
        switch (get_class($value))
        {
            case 'SDate':
                return '<dateTime.iso8601>'.$value->to_iso8601().'</dateTime.iso8601>';
                break;
            case 'SDateTime':
                return '<dateTime.iso8601>'.$value->to_iso8601().'</dateTime.iso8601>';
                break;
            case 'SBase64':
                return $value->__toString();
                break;
            default:
                // if to_array() method exists, the object is an ActiveRecord
                // or a Struct
                if (method_exists($value, 'to_array'))
                    return $this->array_to_xml($value->to_array());
                else
                    return $this->array_to_xml(get_object_vars($value));
                break;
        }
    }
    
    private function array_to_xml($array)
    {
        if ($this->is_struct($array))
        {
            $xml = "<struct>\n";
            foreach ($array as $name => $value)
            {
                $v = new SXmlRpcValue($value);
                $xml.= "  <member><name>$name</name><value>";
                $xml.= $v->to_xml()."</value></member>\n";
            }
            $xml.= '</struct>';
            return $xml;
        }
        else
        {
            $xml = "<array><data>\n";
            foreach ($array as $value)
            {
                $v = new SXmlRpcValue($value);
                $xml.= '  <value>'.$v->to_xml()."</value>\n";
            }
            $xml.= '</data></array>';
            return $xml;
        }
    }
    
    private function is_struct($array)
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

?>
