<?php

class MockRecord
{
    protected $attributes = array();
    protected $values = array();
    
    public function __set($key, $value)
    {
        if (in_array($key, $this->attributes)) $this->values[$key] = $value;
    }
    
    public function __get($key)
    {
        if (isset($this->values[$key])) return $this->values[$key];
        else return null;
    }
}

class DomEqualExpectation extends EqualExpectation {
    
    function test($compare, $nasty = false)
    {
        $docValue = new DOMDocument();
        $docValue->preserveWhiteSpace = false;
        $docValue->loadXML('<root>'.$this->_value.'</root>');
        
        $docCompare = new DOMDocument();
        $docCompare->preserveWhiteSpace = false;
        $docCompare->loadXML('<root>'.$compare.'</root>');
        
        return $this->_domCompare($docValue->documentElement, $docCompare->documentElement);
    }
    
    function _domCompare($value, $compare)
    {
        $valueNodes = $value->childNodes;
        $compareNodes = $compare->childNodes;
        if ($valueNodes->length != $compareNodes->length) return false;
        
        for ($i = 0; $i < $valueNodes->length; $i++)
        {
            $valueNode = $valueNodes->item($i);
            $compareNode = $compareNodes->item($i);
            if ($valueNode->nodeName != $compareNode->nodeName) return false;
            if ($valueNode->nodeType == XML_TEXT_NODE)
            {
                if ($compareNode->nodeType != XML_TEXT_NODE) return false;
                if ($valueNode->nodeValue != $compareNode->nodeValue) return false;
            }
            else
            {
                if (!$this->_domCompareAttributes($valueNode, $compareNode)) return false;
                if ($valueNode->hasChildNodes())
                {
                    if (!$compareNode->hasChildNodes()) return false;
                    if (!$this->_domCompare($valueNode, $compareNode)) return false;
                }
            }
        }
        
        return true;
    }
    
    function _domCompareAttributes($valueNode, $compareNode)
    {
        if ($valueNode->hasAttributes())
        {
            if (!$compareNode->hasAttributes()) return false;
            if ($valueNode->attributes->length 
                != $compareNode->attributes->length) return false;
            $valueNodeAttrs = $valueNode->attributes;
            for ($i = 0; $i < $valueNodeAttrs->length; $i++)
            {
                $attr = $valueNodeAttrs->item($i);
                if (!$compareNode->hasAttribute($attr->nodeName)) return false;
                if ($attr->nodeValue != $compareNode->getAttribute($attr->nodeName)) return false;
            }
        }
        elseif ($compareNode->hasAttributes()) return false;
        
        return true;
    }
}

class HelperTestCase extends UnitTestCase
{
    function assertDomEqual($first, $second, $message = "%s")
    {
        return $this->assertExpectation(new DomEqualExpectation($first), $second, $message);
    }
}

?>
