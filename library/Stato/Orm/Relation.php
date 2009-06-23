<?php

namespace Stato\Orm;

class Relation
{
    
    
    public $mapper;
    public $options;
    
    public function __construct($arg, $options = array())
    {
        if ($arg instanceof Mapper) $this->mapper = $arg;
        else $this->mapper = Mapper::getClassMapper($arg);
        
        $this->options = array_merge(self::$defaultOptions, $options);
    }
}