<?php

interface SAroundFilter
{
    public function before($controller);
    
    public function after($controller);
}

interface SIFilterable
{
    public function call_filter($method, $state);
}

class SFilters
{
    public static function process($object, $state, $method_to_perform, $filters)
    {
        foreach ($filters as $filter => $options)
        {
            if (isset($options['only']) && !is_array($options['only']))
                $options['only'] = array($options['only']);
            if (isset($options['except']) && !is_array($options['except']))
                $options['except'] = array($options['except']);
            
            if ((!isset($options['only']) && !isset($options['except']))
                || (isset($options['only']) && in_array($method_to_perform, $options['only']))
                || (isset($options['except']) && !in_array($method_to_perform, $options['except'])))
                $result = $object->call_filter($filter, $state);
            
            if (@$result === false) return false;
        }
    }
}

?>
