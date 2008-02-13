<?php

class SRoutingException extends Exception {}

class SComponent
{
    protected $key = null;
    protected $optional = false;
    
    public function is_dynamic()
    {
        return false;
    }
    
    public function is_optional()
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
    
    public function write_generation($url)
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
    
    public function set_default($default)
    {
        $this->default = $default;
        $this->optional = true;
    }
    
    public function is_dynamic()
    {
        return true;
    }
    
    public function write_generation($url)
    {
        if (isset($url->params[$this->key]))
        {
            if ($url->params[$this->key] != $this->default)
                $url->parts[] = $url->params[$this->key];
            elseif (!empty($url->parts) && $url->parts[count($url->parts)-1] !== false)
                $url->parts[] = $this->default;
            
            unset($url->params[$this->key]);
        }
        elseif ($this->default !== null && !$this->is_optional())
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
    
    public function regex()
    {
        return '(?P<'.$this->key.'>[a-z0-9_/-]*)';
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
    
    public function has_gaps()
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
    public $path_keys  = array();
    public $keys       = array();
    public $defaults   = array();
    public $regex      = null;
    public $subdir     = null;
    
    public function __construct($path, $options = array())
    {
        $this->path = $path;
        $this->options = $options;
        $this->initialize_components($path);
        list($defaults, $conditions) = $this->initialize_hashes($options);
        $this->defaults = $defaults;
        $this->configure_components($defaults, $conditions);
    }
    
    public function generate($options)
    {
        $url = new SUrl($options);
        
        $comps = array_reverse($this->components);
        foreach ($comps as $comp) $comp->write_generation($url);
        $url->parts = array_reverse($url->parts);
        
        if ($url->has_gaps())
            throw new SRoutingException("No url can be generated for the options : ".serialize($options));
            
        foreach($this->known as $k => $v)
            if (array_key_exists($k, $url->params)) unset($url->params[$k]);
        
        return array($url->__toString(), $url->params);
    }
    
    public function write_regex()
    {
        list($regex, $optional) = $this->build_recursive_regex($this->components);
        $this->regex = '#^'.$regex.'$#i';
    }
    
    protected function build_recursive_regex($components)
    {
        $rest = null;
        $optional = true;
        
        $comp = array_shift($components);
        if (count($components) > 0)
            list($rest, $optional) = $this->build_recursive_regex($components);
            
        if (empty($rest)) $regex = $comp->regex();
        else
        {
            if ($optional)
                $regex = $comp->regex()."(/$rest)?";
            else
                $regex = $comp->regex()."/$rest";
        }
        
        if ($comp->is_optional() && $optional)
            return array($regex, true);
        else
            return array($regex, false);
    }
    
    protected function initialize_components($path)
    {
        if (is_string($path)) $path = explode('/', $path);
        foreach ($path as $str)
        {
            $comp = SComponent::instanciate($str);
            $this->components[] = $comp;
            $this->path_keys[] = $comp->key();
        }
    }
    
    protected function initialize_hashes($options)
    {
        $conditions = array();
        $defaults   = array();
        
        if (isset($options['requirements']))
        {
            $conditions = $options['requirements'];
            unset($options['requirements']);
        }
        
        foreach ($options as $k => $v)
        {
            if (in_array($k, $this->path_keys))
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
    
    protected function configure_components($defaults, $conditions)
    {
        foreach ($this->components as $comp)
        {
            if (array_key_exists($comp->key(), $defaults)) $comp->set_default($defaults[$comp->key()]);
            elseif ($comp->key() == 'action') $comp->set_default('index');
            elseif ($comp->key() == 'id') $comp->set_default(null);
            
            if (array_key_exists($comp->key(), $conditions))
                $comp->condition = $conditions[$comp->key()];
        }
    }
}

class SRouteSet
{
    private $routes  = array();
    private $gen_map = array();
    
    public function connect($path, $options = array())
    {
        $route = new SRoute($path, $options);
        $this->routes[] = $route;
        return $route;
    }
    
    public function __call($method, $args)
    {
        $route = call_user_func_array(array($this, 'connect'), $args);
        SNamedRoutes::connect($method, $route);
        return $route;
    }
    
    public function generate($options)
    {
        if (!isset($this->gen_map[$options['controller']]))
        {
            $actions = $this->gen_map['*'];
        }
        else $actions = $this->gen_map[$options['controller']];
        
        if (!isset($options['action']) || !isset($actions[$options['action']]))
        {
            if (isset($actions['*'])) $route = $actions['*'];
            else $route = $this->gen_map['*']['*'];
        }
        else $route = $actions[$options['action']];
        
        return $route->generate($options);
    }
    
    public function recognize_path($path)
    {
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
                
                $recognized = true;
                break;
            }
        }
        if (!$recognized) return null;
        return $options;
    }
    
    public function draw()
    {
        foreach ($this->routes as $r)
        {
            $r->write_regex();
            
            if (array_key_exists('controller', $r->known))
            {
                if (array_key_exists('action', $r->known))
                    $this->gen_map[$r->known['controller']][$r->known['action']] = $r;
                else
                    $this->gen_map[$r->known['controller']]['*'] = $r;
            }
            else $this->gen_map['*']['*'] = $r;
        }
    }
    
    private function extract_format($path)
    {
        return strrchr($path, '.');
    }
}

class SRoutes
{
    private static $map = null;
    
    public static function initialize($map, $use_named_routes = true)
    {
        self::$map = $map;
        self::$map->draw();
        
        if ($use_named_routes) SNamedRoutes::install();
    }
  
    public static function recognize($request)
    {
        $url = $request->request_uri();
        
        if (strpos($url, '?') !== false)
            list($path, $query_string) = explode('?', $url);
        else
            list($path, $query_string) = array($url, '');
            
        // Skip trailing slash
        if (substr($path, -1) == '/') $path = substr($path, 0, -1);
        
        $extension = strrchr($path, '.');
        if ($extension !== false)
        {
            $request->set_format_by_extension(substr($extension, 1));
            $path = substr($path, 0, - strlen($extension));
        }
            
        $options = self::$map->recognize_path($path);
        
        if ($options === null)
            throw new SRoutingException("Recognition failed for $url");
        
        parse_str($query_string, $params);
        $options = array_merge($params, $options);
        $request->inject_params($options);
            
        return $request;
    }
    
    public static function generate($options)
    {
        return self::$map->generate($options);
    }
}

class SNamedRoutes
{
    private static $helpers = array();
    
    public static function connect($name, $route)
    {
        $options = array_merge($route->known, $route->defaults);
        self::$helpers[] = self::code_for_helper($name, $options);
    }
    
    public static function install()
    {
        $file_path = STATO_APP_ROOT_PATH.'/cache/generated_code/named_routes.php';
        $routes_path = STATO_APP_ROOT_PATH.'/conf/routes.php';
        
        if (!file_exists($file_path) || filemtime($file_path) < filemtime($routes_path))
            file_put_contents($file_path, "<?php\n\n".implode("\n", self::$helpers)."\n?>\n");
            
        require($file_path);
    }
    
    private static function code_for_helper($name, $options)
    {
        $code = "function {$name}_url(".'$options = array()) {'."\n";
        $code.= '    if (!is_array($options)) $options = array($options);'."\n";
        $code.= '    $defaults = array('.self::code_for_defaults_array($options).");\n";
        $code.= '    return url_for(array_merge($defaults, $options));'."\n}\n";
        return $code;
    }
    
    private static function code_for_defaults_array($options)
    {
        $code = array();
        foreach ($options as $k => $v) $code[] = "'{$k}' => '{$v}'";
        return implode(', ', $code);
    }
}

?>
