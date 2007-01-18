<?php

class SBase64
{
    private $value = null;
    
    public function __construct($value, $already_encoded = false)
    {
        $value = (string) $value;
        if ($already_encoded) $this->value = $value;
        else $this->value = base64_encode($value);
    }
    
    public function __toString()
    {
        return $this->value;
    }
}

?>
