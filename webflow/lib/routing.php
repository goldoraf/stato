<?php

class Stato_RoutingError extends Exception {}

class Stato_RouteSet
{
    private $recognizers;
    
    public function __construct()
    {
        $this->recognizers = array();
    }
    
    public function addRoute($path, $args = array())
    {
        $requirements = array();
        if (array_key_exists('requirements', $args)) {
            $requirements = $args['requirements'];
            unset($args['requirements']);
        }
        
        $params = array();
        $regexParts = array();
        $segments = explode('/', $path);
        foreach ($segments as $k => $segment) {
            if (preg_match('/^:(\w+)$/', $segment, $m)) {
                $param = $m[1];
                $params[] = $param; // useful ?
                if (array_key_exists($param, $args)) {
                    $this->addRecognizer($regexParts, $args);
                    unset($args[$param]);
                }
                $requirement = (array_key_exists($param, $requirements)) 
                             ? $requirements[$param] : '\w+';
                $regexParts[] = "(?P<{$param}>{$requirement})";
                $this->addRecognizer($regexParts, $args);
            } else {
                $regexParts[] = $segment;
            }
            
        }print_r($this->recognizers);
    }
    
    public function recognizePath($path)
    {
        foreach ($this->recognizers as $regex => $defaults) {
            if (preg_match($regex, $path, $matches)) {
                // Removes numeric keys from the matches array
                foreach($matches as $key => $match)
                    if (is_int($key)) unset($matches[$key]);
                    
                return array_merge($defaults, $matches);
            }
        }
        
        throw new Stato_RoutingError("Recognition failed for $path");
    }
    
    private function addRecognizer($regexParts, $defaults)
    {
        $regex = '|^'.implode('/', $regexParts).'$|';
        $this->recognizers[$regex] = $defaults;
    }
}