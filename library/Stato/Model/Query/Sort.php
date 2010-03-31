<?php

namespace Stato\Model\Query;

class Sort
{
    public $subject;
    
    public $ascending;
    
    public function __construct($subject, $ascending = true)
    {
        $this->subject = $subject;
        $this->ascending = $ascending;
    }
}