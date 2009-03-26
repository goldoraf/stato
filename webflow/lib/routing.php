<?php

class Stato_RoutingError extends Exception {}

class Stato_RouteSet
{
    private $routes;
    
    private $routeSets;
    
    private $recognizers;
    
    private $segmentSeparators;
    
    public function __construct()
    {
        $this->routes = array();
        $this->routeSets = array();
        $this->segmentSeparators = array('/', '\.');
    }
    
    public function addRouteSet($endPointPath, Stato_RouteSet $set)
    {
        $this->routeSets[$endPointPath] = $set;
    }
    
    public function addRoute($path, $defaults = array(), $requirements = array())
    {
        $params = array();
        $regexParts = array();
        $segments = preg_split('#'.implode('|', $this->segmentSeparators).'#', $path, -1, PREG_SPLIT_OFFSET_CAPTURE);
        $firstOptionalSegment = 0;
        foreach ($segments as $k => $segment) {
            $separator = ($segment[1] === 0) ? '' : $path[$segment[1]-1];
            if (preg_match('/^:(\w+)$/', $segment[0], $m)) {
                $params[] = $param = $m[1];
                $requirement = (array_key_exists($param, $requirements)) 
                             ? $requirements[$param] : '\w+';
                $regexParts[] = "{$separator}(?P<{$param}>{$requirement})";
            } elseif (preg_match('/^\*(\w+)$/', $segment[0], $m)) {
                $params[] = $param = $m[1];
                $regexParts[] = "{$separator}(?P<{$param}>[a-z0-9_/-]*)";
                break;
            } else {
                $regexParts[] = $separator.$segment[0];
                $firstOptionalSegment = $k + 1;
            }
        }
        $route = new Stato_Route($regexParts, $params, $defaults);
        $route->firstOptionalSegment = $firstOptionalSegment;
        $this->routes[] = $route;
    }
    
    public function recognizePath($path)
    {
        $path = ltrim($path, '/');
        
        if (!isset($this->recognizers)) $this->buildRecognizers();
        
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
    
    public function buildRecognizers()
    {
        $this->recognizers = array();
        foreach ($this->routes as $route) {
            $regexParts = array();
            if ($route->firstOptionalSegment == 0)
                    $this->addRecognizer($this->buildRegex($regexParts), $route->defaults);
            foreach ($route->segments as $k => $segment) {
                if ($k >= $route->firstOptionalSegment)
                    $this->addRecognizer($this->buildRegex($regexParts), $route->defaults);
                if (!empty($segment)) $regexParts[] = $segment;
            }
            $this->addRecognizer($this->buildRegex($regexParts), $route->defaults);
        }
        foreach ($this->routeSets as $endPointPath => $routeSet) {
            $recognizers = $routeSet->getRecognizers();
            foreach ($recognizers as $regex => $defaults) {
                $newRegex = str_replace('|^', '|^'.$endPointPath, $regex);
                $this->addRecognizer($newRegex, $defaults);
            }
        }
    }
    
    public function getRecognizers()
    {
        if (!isset($this->recognizers)) $this->buildRecognizers();
        return $this->recognizers;
    }
    
    public function setSegmentSeparators($separators)
    {
        $this->segmentSeparators = $separators;
    }
    
    private function buildRegex($regexParts)
    {
        return '|^'.implode('', $regexParts).'$|';
    }
    
    private function addRecognizer($regex, $defaults)
    {
        if (array_key_exists($regex, $this->recognizers)) return;
        $this->recognizers[$regex] = $defaults;
    }
}

class Stato_Route
{
    public $segments;
    public $params;
    public $defaults;
    public $firstOptionalSegment = 0;
    
    public function __construct($segments, $params, $defaults)
    {
        $this->segments = $segments;
        $this->params = $params;
        $this->defaults = $defaults;
    }
}