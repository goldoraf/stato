<?php

class Routes
{
    private static $regexRoutes = array();
    private static $routesMap   = array();
    
    public static function initialize()
    {
        $routes = include(ROOT_DIR.'/conf/routes.php');
        
        foreach($routes as $regex => $options)
        {
            $pattern = self::convertRegex($regex, $options['validate']);
            unset($options['validate']);
            self::$regexRoutes[$pattern] = $options;
            self::$routesMap[$options['module']][$options['controller']][$options['action']] = $regex;
        }print_r(ROOT_DIR.'/conf/routes.php');
    }
    
    public static function rewriteUrl($options)
    {
        if (isset(self::$routesMap[$options['module']][$options['controller']][$options['action']]))
        {
            $regex = self::$routesMap[$options['module']][$options['controller']][$options['action']];
            return BASE_DIR.'/'.preg_replace('/\{(\w+)\}/e', "self::getOption('\\1', \$options)", $regex);
        }
        else
        {
            $url = BASE_DIR.'/'.$options['module'].'/'.$options['controller'].'/'.$options['action'];
            foreach ($options as $key => $value)
            {
                if (!in_array($key, array('module', 'controller', 'action'))) $url.= "/{$key}/{$value}";
            }
            return $url;
        }
    }
    
    public static function parseUrl($url)
    {
        foreach(self::$regexRoutes as $regex => $options)
        {
            if (preg_match($regex, $url, $matches))
            {
                // Removes numeric keys from the matches array
                foreach($matches as $key => $match)
                {
                    if (is_int($key)) unset($matches[$key]);
                }
                return array_merge($options, $matches);
            }
        }
        // Else...
        $set = explode('/', $url);
        $options['module']     = $set[0];
        $options['controller'] = $set[1];
        $options['action']     = $set[2];
        for ($i = 3; $i < $count = count($set); $i = $i+2) $options[$set[$i]] = $set[$i+1];
        
        return $options;
    }
    
    public static function convertRegex($regex, $validate)
    {
        return '#^'.preg_replace('/\{(\w+)\}/e', "self::getVarRegex('\\1', \$validate)", $regex).'$#i';
    }
    
    private static function getVarRegex($key, $validate)
    {
        return "(?P<{$key}>{$validate[$key]})";
    }
    
    private static function getOption($key, $options)
    {
        return $options[$key];
    }
}

?>
