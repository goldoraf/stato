<?php

class SUnkownFormat extends Exception {}

interface SISerializable
{
    public function serializable_form($options = array());
}

abstract class SAbstractSerializer
{
    abstract public function serialize($data);
    
    public static function instantiate($format)
    {
        $serializer_class = "S{$format}Serializer";
        if (!class_exists($serializer_class, false))
            throw new SUnkownFormat($format);
        
        return new $serializer_class();
    }
}

class SJsonSerializer extends SAbstractSerializer
{
    public function serialize($data)
    {
        if (is_object($data) && get_class($data) == 'SQuerySet')
            return $this->serialize_queryset($data);
        
        if (is_object($data) && method_exists($data, 'serializable_form'))
            $data = $data->serializable_form();    
        
        return json_encode($data);
    }
    
    private function serialize_queryset($qs)
    {
        $records = array();
        foreach ($qs as $record) $records[] = $this->serialize($record);
        return '['.implode(',', $records).']';
    }
}

class SXmlSerializer extends SAbstractSerializer
{
    public function serialize($data)
    {
        if (is_object($data))
        {
            if (get_class($data) == 'SQuerySet')
                $start_element = SInflection::underscore(get_class($data->first()));
            else
                $start_element = SInflection::underscore(get_class($data));
        }
        else $start_element = 'result';
        
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $root = $dom->createElement($start_element);
        $dom->appendChild($root);
        $this->recurse_node($data, $dom, $root);
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
    
    private function recurse_node($data, $dom, $parent)
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
            
            if (is_array($value))
            {
                $node = $dom->createElement($key);
                $parent->appendChild($node);
                $this->recurse_node($value, $dom, $node);
            }
            elseif (is_object($value))
            {
                $node = $dom->createElement(SInflection::underscore(get_class($value)));
                $parent->appendChild($node);
                $this->recurse_node($value, $dom, $node);
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