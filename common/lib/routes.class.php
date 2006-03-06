<?php

class SRoutingException extends SException {}

class SRoutes
{
    private static $regexRoutes = array();
    private static $routesMap   = array();
    
    public static function connect($pattern, $options)
    {
        $regex = self::convertRegex($pattern, $options['validate']);
        unset($options['validate']);
        if (!isset($options['action'])) $options['action'] = 'index';
        self::$regexRoutes[$regex] = $options;
        self::$routesMap[$options['controller'].'/'.$options['action']] = $pattern;
    }
    
    public static function recognize($request)
    {
        $options = self::recognizePath($request->requestUri());
        
        $request->controller = $options['controller'];
        $request->action     = $options['action'];
        $request->params     = array_merge($options, $request->params);
        
        if (empty($request->controller))
            throw new SRoutingException('No controller specified in this request !');
            
        return $request;
    }
    
    public static function generate($options)
    {
        if (isset(self::$routesMap[$options['controller'].'/'.$options['action']]))
        {
            $regex = self::$routesMap[$options['controller'].'/'.$options['action']];
            return array(preg_replace('/\{(\w+)\}/e', "self::getOption('\\1', \$options)", $regex), array());
        }
        else
        {
            $url = $options['controller'];
            if ($options['action'] != 'index') $url.= '/'.$options['action'];
            
            foreach(array('controller', 'action') as $opt) unset($options[$opt]);
            return array($url, $options);
        }
    }
    
    private static function recognizePath($url)
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
        list($path, $queryString) = explode('?', $url);
        $set = explode('/', $path);
        
        $options['controller'] = $set[0];
        $options['action']     = $set[1];
        
        parse_str($queryString, $params);
        return array_merge($params, $options);
    }
    
    private static function convertRegex($regex, $validate)
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
