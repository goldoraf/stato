<?php

class SUrlRewriter
{
    private static $request = null;
    private static $reserved_options = array('only_path', 'protocol', 'host', 'anchor',
        'trailing_slash', 'skip_relative_url_root', 'action_suffix');
        
    public static function initialize($request)
    {
        self::$request = $request;
    }
    
    public static function current_params()
    {
        return self::$request->params;
    }
    
    public static function request_param($param)
    {
        if (isset(self::$request->params[$param])) return self::$request->params[$param];
        return null;
    }
    
    public static function request_uri()
    {
        return self::$request->request_uri();
    }
    
    public static function is_current_page($options)
    {
        $options['only_path'] = true;
        $options['skip_relative_url_root'] = true;
        return self::url_for($options) == self::request_uri();
    }
    
    public static function is_current_controller($controller_name)
    {
        return $controller_name == self::$request->params['controller'];
    }
    
    public static function current_module()
    {
        return self::$request->params['module']; 
    }
    
    public static function current_controller()
    {
        return self::$request->params['controller']; 
    }
    
    public static function url_for($options)
    {
        if (!is_array($options)) $options = array($options);
        
        foreach ($options as $k => $v)
        {
            if (is_object($v))
            {
                $ref = new ReflectionObject($v);
                if ($ref->isSubclassOf(new ReflectionClass('SActiveRecord')))
                {
                    unset($options[$k]);
                    $options['id'] = $v->id;
                }
            }
        }
        
        if (!isset($options['action']))     $options['action'] = 'index';
        if (!isset($options['controller'])) $options['controller'] = self::$request->params['controller'];
        
        return self::rewrite($options);
    }
    
    public static function rewrite($options = array())
    {
        return self::rewrite_url(self::rewrite_path($options), $options);
    }
    
    private static function rewrite_url($path, $options)
    {
        $url = '';
        
        if (SActionController::$use_relative_urls === false && (!isset($options['only_path']) || $options['only_path'] == false))
        {
            $url.= isset($options['protocol']) ? $options['protocol'] : self::$request->protocol();
            $url.= isset($options['host']) ? $options['host'] : self::$request->host_with_port();
        }
        if (!isset($options['skip_relative_url_root']) || $options['skip_relative_url_root'] == false)
            $url.= self::$request->relative_url_root();
        
        $url.= $path;
        if (isset($options['action_suffix'])) $url.= '/'.$options['action_suffix'];
        if (isset($options['trailing_slash'])) $url.= '/';
        if (isset($options['anchor'])) $url.= '#'.$options['anchor'];
        
        return $url;
    }
    
    private static function rewrite_path($options)
    {
        foreach(self::$reserved_options as $opt) unset($options[$opt]);
        
        if (isset($options['params']))
        {
            foreach ($options['params'] as $key => $value) $options[$key] = $value;
            unset($options['params']);
        }
        
        list($path, $extra_keys) = SRoutes::generate($options);
        
        if (!empty($extra_keys)) $path.= self::build_query_string($extra_keys);
        
        return $path;
    }
    
    private static function build_query_string($options)
    {
        $string = '';
        $elements = array();
        foreach ($options as $key => $value) $elements[] = "{$key}={$value}";
        if (!empty($elements)) $string.= '?'.implode('&', $elements);
        return $string;
    }
}

?>
