<?php

namespace Stato\Webflow;

class FilterChain
{
    private $filters;
    private $objectFilters;
    
    public function __construct()
    {
        $this->filters = array();
        $this->objectFilters = array();
    }
    
    public function append($filter, $options = array())
    {
        if (is_object($filter)) $this->objectFilters[] = $filter;
        else $this->filters[$filter] = $this->checkOptions($options);
    }
    
    public function skip($filter, $options = array())
    {
        if (!isset($this->filters[$filter])) return;
        if (!isset($options['except']) && !isset($options['only'])) {
            unset($this->filters[$filter]);
            return;
        }
        
        $options = $this->checkOptions($options);
        
        if (!empty($options['except']))
            $this->filters[$filter]['only'] += $options['except'];
        elseif (!empty($options['only']))
            $this->filters[$filter]['except'] += $options['only'];
    }
    
    public function process($object, $methodToPerform, $state)
    {
        foreach ($this->filters as $filter => $options) {
            if (in_array($methodToPerform, $options['only'])
                || (count($options['only']) == 0 && !in_array($methodToPerform, $options['except']))) {
                $result = $object->callFilter($filter);
                if ($result === false) return false;
            }
        }
        
        foreach ($this->objectFilters as $filter) {
            if (method_exists($filter, $state)) $result = $filter->$state($object);
            else $result = $filter->filter($object);
            if ($result === false) return false;
        }
    }
    
    private function checkOptions($options)
    {
        foreach (array('only', 'except') as $k) {
            if (isset($options[$k]) && !is_array($options[$k]))
                $options[$k] = array($options[$k]);
            elseif (!isset($options[$k]))
                $options[$k] = array();
        }      
        return $options;
    }
}
