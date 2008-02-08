<?php

class SUnkownFormat extends Exception {}

interface SISerializable
{
    public function serializable_form($options = array());
}

class SerializableError
{
    public $error_message;
    public $status_code;
    public $errors;
}

abstract class SAbstractSerializer
{
    abstract public function serialize($data, $options = array());
    
    public static function instantiate($format)
    {
        $serializer_class = "S{$format}Serializer";
        if (!class_exists($serializer_class, false))
            throw new SUnkownFormat($format);
        
        return new $serializer_class();
    }
    
    public static function serialize_exception($format, $exception)
    {
        try {
            $serializer = self::instantiate($format);
        } catch (SUnkownFormat $e) {
            return null;
        }
        
        $error = new SerializableError();
        $error->error_message = $exception->getMessage();
        if (method_exists($exception, 'getErrors'))
            $error->errors = $exception->getErrors();
            
        return $serializer->serialize($error);
    }
}

class SJsonSerializer extends SAbstractSerializer
{
    public function serialize($data, $options = array())
    {
        if (is_object($data) && get_class($data) == 'SQuerySet')
            return $this->serialize_queryset($data, $options);
        
        if (is_object($data) && method_exists($data, 'serializable_form'))
            $data = $data->serializable_form();
        
        return json_encode($data);
    }
    
    private function serialize_queryset($qs, $options)
    {
        $records = array();
        foreach ($qs as $record) $records[] = $this->serialize($record, $options);
        return '['.implode(',', $records).']';
    }
}

class SXmlSerializer extends SAbstractSerializer
{
    public function serialize($data, $options = array())
    {
        if (is_object($data))
        {
            if (get_class($data) == 'SQuerySet')
                $start_element = SInflection::underscore(get_class($data->first()));
            else
                $start_element = SInflection::underscore(get_class($data));
        }
        else $start_element = 'result';
        
        if (!isset($options['dasherize']) || $options['dasherize'] = true)
            $start_element = SInflection::dasherize($start_element);
        
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $root = $dom->createElement($start_element);
        $dom->appendChild($root);
        $this->recurse_node($data, $dom, $root, $options);
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
    
    private function recurse_node($data, $dom, $parent, $options)
    {
        if (!is_array($data) && !is_object($data))
        {
            $node = $dom->createTextNode((string) $data);
            $parent->appendChild($node);
            return;
        }
        
        if (is_object($data) && method_exists($data, 'serializable_form'))
            $data = $data->serializable_form();
        
        foreach ($data as $key => $value)
        {
            if (is_numeric($key)) $key = 'value';
            if (is_object($value)) $key = SInflection::underscore(get_class($value));
            
            if (!isset($options['dasherize']) || $options['dasherize'] = true)
                $key = SInflection::dasherize($key);
            
            if (is_array($value) || is_object($value))
            {
                $node = $dom->createElement($key);
                $parent->appendChild($node);
                $this->recurse_node($value, $dom, $node, $options);
            }
            else
            {
                $node = $dom->createElement($key, $value);
                $parent->appendChild($node);
            }
        }
    }
}

?>