<?php

class SUrlRewriter
{
    private static $request = null;
    private static $reservedOptions = array('only_path', 'protocol', 'host', 'anchor',
        'trailing_slash', 'skip_relative_url_root');
        
    public static function initialize($request)
    {
        self::$request = $request;
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
        if (isset($options['trailing_slash'])) $url.= '/';
        if (isset($options['anchor'])) $url.= '#'.$options['anchor'];
        
        return $url;
    }
    
    private static function rewritePath($options)
    {
        foreach(self::$reservedOptions as $opt) unset($options[$opt]);
        
        list($path, $extraKeys) = SRoutes::generate($options);
        
        if (!empty($extraKeys)) $path.= self::buildQueryString($options);
        
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
