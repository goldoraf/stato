<?php

class SUrlRewriter
{
    private static $request = null;
    private static $reservedOptions = array('only_path', 'protocol', 'host', 'anchor',
        'trailing_slash', 'skip_relative_url_root', 'action_suffix');
        
    public static function initialize($request)
    {
        self::$request = $request;
    }
    
    public static function isCurrentPage($options)
    {
        $options['only_path'] = true;
        $options['skip_relative_url_root'] = true;
        return self::urlFor($options) == self::$request->requestUri();
    }
    
    public static function urlFor($options)
    {
        if (!isset($options['action']))     $options['action'] = 'index';
        if (!isset($options['controller'])) $options['controller'] = self::$request->controller;
        
        return self::rewrite($options);
    }
    
    public static function rewrite($options = array())
    {
        return self::rewriteUrl(self::rewritePath($options), $options);
    }
    
    private static function rewriteUrl($path, $options)
    {
        $url = '';
        
        if (!isset($options['only_path']) || $options['only_path'] == false)
        {
            $url.= isset($options['protocol']) ? $options['protocol'] : self::$request->protocol();
            $url.= isset($options['host']) ? $options['host'] : self::$request->hostWithPort();
        }
        if (!isset($options['skip_relative_url_root']) || $options['skip_relative_url_root'] == false)
            $url.= self::$request->relativeUrlRoot();
        
        $url.= $path;
        if (isset($options['action_suffix'])) $url.= '/'.$options['action_suffix'];
        if (isset($options['trailing_slash'])) $url.= '/';
        if (isset($options['anchor'])) $url.= '#'.$options['anchor'];
        
        return $url;
    }
    
    private static function rewritePath($options)
    {
        foreach(self::$reservedOptions as $opt) unset($options[$opt]);
        
        if (isset($options['params']))
        {
            foreach ($options['params'] as $key => $value) $options[$key] = $value;
            unset($options['params']);
        }
        
        list($path, $extraKeys) = SRoutes::generate($options);
        
        if (!empty($extraKeys)) $path.= self::buildQueryString($extraKeys);
        
        return $path;
    }
    
    private static function buildQueryString($options)
    {
        $string = '';
        $elements = array();
        foreach ($options as $key => $value) $elements[] = "{$key}={$value}";
        if (!empty($elements)) $string.= '?'.implode('&', $elements);
        return $string;
    }
}

?>
