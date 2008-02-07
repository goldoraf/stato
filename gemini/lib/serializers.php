<?php

interface SISerializable
{
    public function serializable_form($options = array());
}

abstract class SAbstractSerializer
{
    public function serialize($variable)
    {
        if (is_object($variable) && method_exists($variable, 'serializable_form'))
            $variable = $variable->serializable_form();
            
        return $this->handle($variable);
    }
    
    abstract protected function handle($array);
}

class SJsonSerializer extends SAbstractSerializer
{
    protected function handle($array)
    {
        return json_encode($array);
    }
}

class SXmlSerializer extends SAbstractSerializer
{
    protected function handle($array)
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $root = $dom->createElement('result');
        $dom->appendChild($root);
        $this->recurse_node($array, $dom, $root);
        return $dom->saveXML();
    }
    
    private function recurse_node($data, $dom, $parent)
    {
        foreach ($data as $key => $value)
        {
            if (is_array($value))
            {
                $node = $dom->createElement($key);
                $parent->appendChild($node);
                $this->recurse_node($value, $dom, $node);
            }
            elseif (is_object($value))
            {
                $node = $dom->createElement($key, 'Object: '.get_class($value));
                $parent->appendChild($node);
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