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
        foreach ($filters as $filter)
        {
            if (is_array($filter))
            {
                $method = $filter[0];
                
                if (isset($filter['only']) && !is_array($filter['only']))
                    $filter['only'] = array($filter['only']);
                if (isset($filter['except']) && !is_array($filter['except']))
                    $filter['except'] = array($filter['except']);
                
                if ((isset($filter['only']) && in_array($method_to_perform, $filter['only']))
                    || (isset($filter['except']) && !in_array($method_to_perform, $filter['except']))
                    || (!isset($filter['only']) && !isset($filter['except'])))
                    $result = $object->call_filter($method, $state);
            }
            else $result = $object->call_filter($filter, $state);
            
            if (@$result === false) return false;
        }
    }
}

?>
