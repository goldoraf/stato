<?php

class DomEqualExpectation extends EqualExpectation
{
    
    public function test($compare)
    {
        $docValue = $this->instanciateDomDocument($this->getValue());
        $docCompare = $this->instanciateDomDocument($compare);
        
        return $this->domCompare($docValue->documentElement, $docCompare->documentElement);
    }
    
    private function domCompare($value, $compare)
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
                if (!$this->domCompareAttributes($valueNode, $compareNode)) return false;
                if ($valueNode->hasChildNodes())
                {
                    if (!$compareNode->hasChildNodes()) return false;
                    if (!$this->domCompare($valueNode, $compareNode)) return false;
                }
            }
        }
        
        return true;
    }
    
    private function domCompareAttributes($valueNode, $compareNode)
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
    
    private function instanciateDomDocument($xml)
    {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        if (preg_match('/^<\?xml version="1.0"(.*)\?>(.*)$/sm', $xml, $matches))
            $xml = $matches[2];
        $doc->loadXML('<root>'.$xml.'</root>');
        return $doc;
    }
}

?>
