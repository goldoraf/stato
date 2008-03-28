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
        if (is_object($data)) return $this->serialize_object($data, $options);
        return json_encode($data);
    }
    
    private function serialize_object($object, $options)
    {
        if ($this->implements_iterator($object))
            return $this->serialize_iterator($object, $options);
        
        return $this->recurse_on_object($object, $options);
    }
    
    private function recurse_on_object($object, $options)
    {
        if (method_exists($object, 'serializable_form'))
            $object = $object->serializable_form();
            
        $parts = array();
        foreach ($object as $key => $value)
        {
            if (is_object($value)) $value = $this->serialize_object($value, $options);
            else $value = json_encode($value);
            $parts[] = "\"$key\":$value";
        }
        return '{'.implode(',', $parts).'}';
    }
    
    private function serialize_iterator($iterator, $options)
    {
        $values = array();
        foreach ($iterator as $value) $values[] = $this->serialize($value, $options);
        return '['.implode(',', $values).']';
    }
    
    private function implements_iterator($object)
    {
        $ref = new ReflectionObject($object);
        return $ref->implementsInterface('Iterator');
    }
}

class SXmlSerializer extends SAbstractSerializer
{
    public function serialize($data, $options = array())
    {
        $defaults = array('dasherize' => true, /*'skip_instruct' => false,*/
                          'indent' => true);
        
        $options = array_merge($defaults, $options);
        
        if (is_object($data))
        {
            if (get_class($data) == 'SQuerySet')
                $start_element = SInflection::pluralize(SInflection::underscore(get_class($data->first())));
            else
                $start_element = SInflection::underscore(get_class($data));
        }
        else $start_element = 'result';
        
        if ($options['dasherize'] == true)
            $start_element = SInflection::dasherize($start_element);
        
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        if ($defaults['indent'] === true) $dom->formatOutput = true;
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
            if (is_numeric($key) && is_object($value)) $key = SInflection::underscore(get_class($value));
            elseif (is_numeric($key)) $key = 'value';
            
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
