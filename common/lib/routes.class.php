<?php

class SRoutingException extends SException {}

class SRoutes
{
    private static $modules     = array();
    private static $regexRoutes = array();
    private static $routesMap   = array();
    
    public static function connect($pattern, $options)
    {
        $regex = self::convertRegex($pattern, $options['validate']);
        unset($options['validate']);
        if (!isset($options['module'])) $options['module'] = 'root';
        if (!isset($options['action'])) $options['action'] = 'index';
        self::$regexRoutes[$regex] = $options;
        self::$routesMap[$options['module'].'/'.$options['controller'].'/'.$options['action']] = $pattern;
    }
    
    public static function connectModule($module)
    {
        self::$modules[] = $module;
    }
    
    public static function recognize($request)
    {
        $options = self::recognizePath($request->requestUri());
        
        $request->module     = $options['module'];
        $request->controller = $options['controller'];
        $request->action     = $options['action'];
        $request->params     = array_merge($options, $request->params);
        
        if ($request->module != 'root' && !is_dir(APP_DIR.'/modules/'.$request->module))
            throw new SRoutingException($request->module.' module not found !');
        
        if (empty($request->controller))
            throw new SRoutingException('No controller specified in this request !');
            
        return $request;
    }
    
    public static function generate($options)
    {
        if (isset(self::$routesMap[$options['module'].'/'.$options['controller'].'/'.$options['action']]))
        {
            $regex = self::$routesMap[$options['module'].'/'.$options['controller'].'/'.$options['action']];
            return array(preg_replace('/\{(\w+)\}/e', "self::getOption('\\1', \$options)", $regex), array());
        }
        else
        {
            if ($options['module'] == 'root')
                $url = $options['controller'];
            else
                $url = $options['module'].'/'.$options['controller'];   
            if ($options['action'] != 'index') $url.= '/'.$options['action'];
            
            foreach(array('module', 'controller', 'action') as $opt) unset($options[$opt]);
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
        if (in_array($set[0], self::$modules))
        {
            $options['module']     = $set[0];
            $options['controller'] = $set[1];
            $options['action']     = $set[2];
        }
        else
        {
            $options['module']     = 'root';
            $options['controller'] = $set[0];
            $options['action']     = $set[1];
        }
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
