<?php

interface SAroundFilter
{
    public function before($controller);
    
    public function after($controller);
}

interface SIFilterable
{
    public function call_filter($method);
}

class SFilterChain
{
    private $filters;
    private $object_filters;
    
    public function __construct()
    {
        $this->filters = array();
        $this->object_filters = array();
    }
    
    public function append($filter, $options = array())
    {
        if (is_object($filter)) $this->object_filters[] = $filter;
        else $this->filters[$filter] = $this->check_options($options);
    }
    
    public function skip($filter, $options = array())
    {
        if (!isset($this->filters[$filter])) return;
        if (!isset($options['except']) && !isset($options['only']))
        {
            unset($this->filters[$filter]);
            return;
        }
        
        $options = $this->check_options($options);
        
        if (!empty($options['except']))
            $this->filters[$filter]['only'] += $options['except'];
        elseif (!empty($options['only']))
            $this->filters[$filter]['except'] += $options['only'];
    }
    
    public function process($object, $method_to_perform, $state)
    {
        foreach ($this->filters as $filter => $options)
        {
            if (in_array($method_to_perform, $options['only'])
                || (count($options['only']) == 0 && !in_array($method_to_perform, $options['except'])))
            {
                $result = $object->call_filter($filter);
                if ($result === false) return false;
            }
        }
        
        foreach ($this->object_filters as $filter)
        {
            if (method_exists($filter, $state)) $result = $filter->$state($object);
            else $result = $filter->filter($object);
            if ($result === false) return false;
        }
    }
    
    private function check_options($options)
    {
        foreach (array('only', 'except') as $k)
        {
            if (isset($options[$k]) && !is_array($options[$k]))
                $options[$k] = array($options[$k]);
            elseif (!isset($options[$k]))
                $options[$k] = array();
        }      
        return $options;
    }
}

?>
