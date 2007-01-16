<?php

class SXmlRpcValueException extends SException {}

class SXmlRpcValue
{
    const TYPE_INTEGER  = 'integer';
    const TYPE_FLOAT    = 'float';
    const TYPE_STRING   = 'string';
    const TYPE_DATETIME = 'datetime';
    const TYPE_BOOLEAN  = 'boolean';
    const TYPE_BASE64   = 'base64';
    const TYPE_ARRAY    = 'array';
    const TYPE_OBJECT   = 'object';
    
    public $value = null;
    public $type  = null;
    
    public function __construct($value, $type = null)
    {
        $this->value = $value;
        if ($type === null) $this->type = gettype($this->value);
        else $this->type = $type;
    }
    
    public static function typecast($xml_string)
    {
        try { $xml = new SimpleXMLElement($xml_string); }
        catch (Exception $e) { 
            throw new SXmlRpcValueException("Failed to parse XML value : $xml_string");
        }
        
        list($type, $value) = each($xml);
        if (!$type) $type = 'string';
        
        switch ($type)
        {
            case 'i4':
                // fall through to the next case
            case 'int':
                return new SXmlRpcValue((integer) $value, self::TYPE_INTEGER);
            case 'double':
                return new SXmlRpcValue((float) $value, self::TYPE_FLOAT);
            case 'boolean':
                return new SXmlRpcValue($value == 1, self::TYPE_BOOLEAN);
            case 'string':
                return new SXmlRpcValue($value, self::TYPE_STRING);
            case 'dateTime.iso8601':
                return new SXmlRpcValue(SDateTime::parse($value), self::TYPE_DATETIME);
            case 'base64':
                return new SXmlRpcValue(base64_decode($value), self::TYPE_BASE64);
            case 'array':
                if (!$value instanceof SimpleXMLElement/* || empty($value->data)*/)
                    throw new SXmlRpcValueException('Invalid XML string for array type');
                
                $values = array();
                foreach ($value->data->value as $element)
                    $values[] = self::typecast($element->asXml());
                
                return new SXmlRpcValue($values, self::TYPE_ARRAY);
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
                return new SXmlRpcValue($values, self::TYPE_ARRAY);
            default:
                throw new SXmlRpcValueException("$type is not a native XML-RPC type");
        }
    }
    
    public function to_php()
    {
        if ($this->type != self::TYPE_ARRAY) return $this->value;
        
        $value = array();
        foreach ($this->value as $k => $v)
            $value[$k] = $v->to_php();
            
        return $value;
    }
    
    public function to_xml()
    {
        switch ($this->type)
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
