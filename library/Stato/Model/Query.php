<?php

namespace Stato\Model;

use \Exception;

class Query
{
    private $metaclass;
    
    private $conditions;
    
    private $offset;
    
    private $limit;
    
    private $sorts;
    
    public function __construct(Metaclass $meta)
    {
        $this->metaclass = $meta;
        $this->conditions = array();
        $this->sorts = array();
        $this->offset = 0;
        $this->limit = null;
    }
    
    public function getMetaclass()
    {
        return $this->metaclass;
    }
    
    public function getConditions()
    {
        return $this->conditions;
    }
    
    public function getLimit()
    {
        return $this->limit;
    }
    
    public function getOffset()
    {
        return $this->offset;
    }
    
    public function getSorts()
    {
        return $this->sorts;
    }
    
    public function update(array $options)
    {
        if (array_key_exists('conditions', $options)) {
            $this->mergeConditions($options['conditions']);
        }
        foreach (array('limit', 'offset', 'sorts') as $option) {
            if (array_key_exists($option, $options)) {
                $this->{$option} = $options[$option]; 
            }
        }
        return $this;
    }
    
    private function mergeConditions(array $conditions)
    {
        $this->conditions = array_merge($this->conditions, $conditions);
    }
}