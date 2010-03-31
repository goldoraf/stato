<?php

namespace Stato\Model\Query;

class Condition
{
    public $operator;
    
    public $subject;
    
    public $value;
    
    public $negated = false;
    
    public function __construct($subject, $value, $operator)
    {
        $this->subject  = $subject;
        $this->value    = $value;
        $this->operator = $operator;
    }
    
    public function negate()
    {
        $this->negated = true;
    }
}