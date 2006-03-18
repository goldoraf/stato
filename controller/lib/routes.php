<?php

class SRoutingException extends SException {}

class SComponent
{
    protected $key = null;
    protected $optional = false;
    
    public function isDynamic()
    {
        return false;
    }
    
    public function isOptional()
    {
        return $this->optional;
    }
    
    public function key()
    {
        return $this->key;
    }
    
    public static function instanciate($str)
    {
        if ($str == ':controller') 
            return new SControllerComponent('controller');
        elseif (preg_match('/^:(\w+)$/', $str, $m)) 
            return new SDynamicComponent($m[1]);
        elseif (preg_match('/^\*(\w+)$/', $str, $m))
            return new SPathComponent($m[1]);
        else
            return new SStaticComponent($str);
    }
}

class SStaticComponent extends SComponent
{
    public $value = null;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public function writeGeneration($url)
    {
        $url->parts[] = $this->value;
    }
    
    public function regex()
    {
        return $this->value;
    }
}

class SDynamicComponent extends SComponent
{
    public $default = null;
    public $condition = null;
    
    public function __construct($key)
    {
        $this->key = $key;
    }
    
    public function setDefault($default)
    {
        $this->default = $default;
        $this->optional = true;
    }
    
    public function isDynamic()
    {
        return true;
    }
    
    public function writeGeneration($url)
    {
        if (isset($url->params[$this->key]))
        {
            if ($url->params[$this->key] != $this->default)
                $url->parts[] = $url->params[$this->key];
            elseif (!empty($url->parts) && $url->parts[count($url->parts)-1] !== false)
                $url->parts[] = $this->default;
            
            unset($url->params[$this->key]);
        }
        elseif ($this->default !== null && !$this->isOptional())
            $url->parts[] = $this->default;
        else $url->parts[] = false;
    }
    
    public function regex()
    {
        if (isset($this->condition))
            return '(?P<'.$this->key.'>'.str_replace('/', '', $this->condition).')';
        else
            return '(?P<'.$this->key.'>\w+)';
    }
}

class SControllerComponent extends SDynamicComponent
{
    public $subdir = null;
    
    public function writeGeneration($url)
    {
        if (isset($url->params[$this->key]) && $this->subdir !== null)
            $url->params[$this->key] = str_replace($this->subdir.'/', '', $url->params[$this->key]);
            
        parent::writeGeneration($url);
    }
    
    public function regex()
    {
        return '(?P<'.$this->key.'>\w*)';
    }
}

class SPathComponent extends SDynamicComponent
{
    public function __construct($key)
    {
        $this->key = $key;
        $this->optional = true;
    }
}

class SUrl
{
    public $parts = array();
    public $params = array();
    
    public function __construct($options)
    {
        $this->params = $options;
    }
    
    public function __toString()
    {
        return implode('/', $this->parts);
    }
    
    public function hasGaps()
    {
        $gaps = false;
        foreach ($this->parts as $k => $p)
        {
            if ($gaps && $p !== false) return true;
            if ($p === false)
            {
                unset($this->parts[$k]);
                $gaps = true;
            }
        }
        return false;
    }
}

class SRoute
{
    public $path       = null;
    public $options    = array();
    public $components = array();
    public $known      = array();
    public $pathKeys   = array();
    public $keys       = array();
    public $defaults   = array();
    public $regex      = null;
    public $subdir     = null;
    
    public function __construct($path, $options = array())
    {
        $this->path = $path;
        $this->options = $options;
        $this->initializeComponents($path);
        list($defaults, $conditions) = $this->initializeHashes($options);
        $this->defaults = $defaults;
        $this->configureComponents($defaults, $conditions);
        $this->addDefaultRequirements();
    }
    
    public function generate($options)
    {
        $url = new SUrl($options);
        
        //foreach ($this->components as $comp) $comp->writeGeneration($url);
        $comps = array_reverse($this->components);
        foreach ($comps as $comp) $comp->writeGeneration($url);
        $url->parts = array_reverse($url->parts);
        
        if ($url->hasGaps())
            throw new SRoutingException("No url can be generated for the options : $options");
            
        foreach($this->known as $k => $v)
            if (array_key_exists($k, $url->params)) unset($url->params[$k]);
        
        return array($url->__toString(), $url->params);
    }
    
    public function writeRegex()
    {
        list($regex, $optional) = $this->buildRecursiveRegex($this->components);
        $this->regex = '#^'.$regex.'$#i';
    }
    
    protected function buildRecursiveRegex($components)
    {
        $rest = null;
        $optional = true;
        
        $comp = array_shift($components);
        if (count($components) > 0)
            list($rest, $optional) = $this->buildRecursiveRegex($components);
            
        if (empty($rest)) $regex = $comp->regex();
        else
        {
            if ($optional)
                $regex = $comp->regex()."(/$rest)?";
            else
                $regex = $comp->regex()."/$rest";
        }
        
        if ($comp->isOptional() && $optional)
            return array($regex, true);
        else
            return array($regex, false);
    }
    
    protected function initializeComponents($path)
    {
        if (is_string($path)) $path = explode('/', $path);
        foreach ($path as $str)
        {
            $comp = SComponent::instanciate($str);
            $this->components[] = $comp;
            $this->pathKeys[] = $comp->key();
        }
    }
    
    protected function initializeHashes($options)
    {
        $conditions = array();
        $defaults   = array();
        
        if (isset($options['requirements']))
        {
            $conditions = $options['requirements'];
            unset($options['requirements']);
        }
        
        if (isset($options['subdirectory']))
        {
            if (!in_array('controller', $this->pathKeys))
            {
                throw new SRoutingException('Subdirectory option must be used 
                with a route including a ControllerComponent');
            }
            
            foreach($this->components as $k => $c)
                if ($c->key() == 'controller') $c->subdir = $options['subdirectory'];
            
            $this->subdir = $options['subdirectory'];
            unset($options['subdirectory']);
        }
        
        foreach ($options as $k => $v)
        {
            if (in_array($k, $this->pathKeys))
            {
                if ($v{0} == '/' && $v{strlen($v)-1} == '/') // if $k is a regex
                    $conditions[$k] = $v;
                else
                    $defaults[$k] = $v;
            }
            else $this->known[$k] = $v;
        }
        
        return array($defaults, $conditions);
    }
    
    protected function configureComponents($defaults, $conditions)
    {
        foreach ($this->components as $comp)
        {
            if (array_key_exists($comp->key(), $defaults)) $comp->setDefault($defaults[$comp->key()]);
            elseif ($comp->key() == 'action') $comp->setDefault('index');
            elseif ($comp->key() == 'id') $comp->setDefault(null);
            
            if (array_key_exists($comp->key(), $conditions))
                $comp->condition = $conditions[$comp->key()];
        }
    }
    
    protected function addDefaultRequirements()
    {
        //if (!in_array('action', $this->pathKeys)) $this->known['action'] = 'index';
    }
}

class SRouteSet
{
    private $routes = array();
    private $genMap = array();
    
    public function connect($path, $options = array())
    {
        $this->routes[] = new SRoute($path, $options);
    }
    
    public function generate($options)
    {
        if (!isset($this->genMap[$options['controller']]))
        {
            if (strpos($options['controller'], '/'))
            {
                list($subdir, $contr) = explode('/', $options['controller']);
                $actions = $this->genMap[$subdir.'/*'];
            }
            else
                $actions = $this->genMap['*'];
        }
        else $actions = $this->genMap[$options['controller']];
        
        if (!isset($actions[$options['action']]))
        {
            if (isset($actions['*'])) $route = $actions['*'];
            else $route = $this->genMap['*']['*'];
        }
        else $route = $actions[$options['action']];
        
        return $route->generate($options);
    }
    
    public function recognizePath($url)
    {
        list($path, $queryString) = explode('?', $url);
        $options = array();
        $recognized = false;
        
        foreach($this->routes as $route)
        {
            if (preg_match($route->regex, $path, $matches))
            {
                // Removes numeric keys from the matches array
                foreach($matches as $key => $match)
                    if (is_int($key)) unset($matches[$key]);
                
                if (empty($matches['controller'])) unset($matches['controller']);
                
                $options = array_merge($route->defaults, $matches);
                $options = array_merge($route->known, $options);
                
                if ($route->subdir !== null)
                    $options['controller'] = $route->subdir.'/'.$options['controller'];
                
                $recognized = true;
                
                break;
            }
        }
        
        if (!$recognized) return null;
        
        parse_str($queryString, $params);
        return array_merge($params, $options);
    }
    
    public function draw()
    {
        foreach ($this->routes as $r)
        {
            $r->writeRegex();
            
            if (array_key_exists('controller', $r->known))
            {
                if (array_key_exists('action', $r->known))
                    $this->genMap[$r->known['controller']][$r->known['action']] = $r;
                else
                    $this->genMap[$r->known['controller']]['*'] = $r;
            }
            else
            {
                if ($r->subdir !== null)
                    $this->genMap[$r->subdir.'/*']['*'] = $r;
                else
                    $this->genMap['*']['*'] = $r;
            }
        }
    }
}

class SRoutes
{
    private static $map = null;
    
    public function initialize($map)
    {
        self::$map = $map;
        self::$map->draw();
    }
  
    public static function recognize($request)
    {
        $options = self::$map->recognizePath($request->requestUri());
        
        if ($options === null)
            throw new SRoutingException('Recognition failed for '.$request->requestUri());
        
        $request->controller = $options['controller'];
        $request->action     = $options['action'];
        $request->params     = array_merge($options, $request->params);
        
        if (empty($request->controller))
            throw new SRoutingException('No controller specified in this request !');
            
        return $request;
    }
    
    public static function generate($options)
    {
        return self::$map->generate($options);
    }
}

?>
