<?php

class Routes
{
    private static $modules     = array();
    private static $regexRoutes = array();
    private static $routesMap   = array();
    
    public static function initialize($configPath = Null)
    {
        if ($configPath == Null) $configPath = ROOT_DIR.'/conf/routes.php';   
        $routes = include($configPath);
        foreach($routes as $regex => $options) self::connect($regex, $options);
    }
    
    public static function connect($pattern, $options)
    {
        $regex = self::convertRegex($pattern, $options['validate']);
        unset($options['validate']);
        self::$regexRoutes[$regex] = $options;
        self::$routesMap[$options['module']][$options['controller']][$options['action']] = $pattern;
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
    
    /*private static function registerModules()
    {
        $folder = new DirectoryIterator(APP_DIR.'/modules');
        foreach($folder as $file) echo $file;
    }*/
    
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
