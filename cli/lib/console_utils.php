<?php

class SConsoleException extends Exception {}

class SConsoleUtils
{
    /**
     * Parses the command-line options and parameters.
     *
     * The first parameter to this method should be the list of command-line
     * arguments without the leading reference to the running script.
     *
     * The second parameter is an array of allowed options, with option names
     * as keys, and boolean values indicating if the corresponding option require
     * an argument or not. Ex : array('version' => true, 'recursive' => false)  
     * This method parses long and short options, ie --version=24 or -v 24             
     *
     * The third argument is an optional array of allowed parameters, with names
     * as keys, and boolean values indicating if the parameter is required.
     *
     * The return value is an array of two elements: the list of parsed
     * options and the list of parsed parameters.
     *      
     * Long and short options can be mixed.
     */
    public static function get_options_and_params($args, $allowed_options, $allowed_params = array())
    {
        $options = array();
        $params  = array();
        
        $allowed_short_options = array();
        foreach ($allowed_options as $o => $v) $allowed_short_options[$o{0}] = $o;
        
        $non_options = array();
        
        while (count($args))
        {
            if ($args[0]{0} == '-')
            {
                if ($args[0]{1} == '-')
                    list($k, $v) = self::parse_long_option(substr(array_shift($args), 2), $allowed_options);
                else
                    list($k, $v) = self::parse_short_option(array_shift($args), $allowed_short_options, $allowed_options, $args);
                    
                $options[$k] = $v;
            }
            else $non_options[] = array_shift($args);
        }
        
        foreach ($allowed_params as $name => $required)
        {
            if (count($non_options) == 0)
            {
                if ($required) throw new SConsoleException("$name parameter missing");
                else break;
            }
            $params[$name] = array_shift($non_options);
        }
        
        return array($options, $params);
    }
    
    private static function parse_long_option($arg, $allowed_options)
    {
        if (strpos($arg, '=') !== false) list($key, $value) = explode('=', $arg);
        else {
            $key = $arg;
            $value = true;
        }
        if (!isset($allowed_options[$key]))
            throw new SConsoleException("Unknown option $key");
        
        if ($allowed_options[$key] && $value === true)
            throw new SConsoleException("Parameter missing for the $key option");
            
        return array($key, $value);
    }
    
    private static function parse_short_option($arg, $allowed_short_options, $allowed_options, &$args)
    {
        $key = $arg{1};
        if (!array_key_exists($key, $allowed_short_options))
            throw new SConsoleException("Unknown option $key");
            
        $key = $allowed_short_options[$key];
        if ($allowed_options[$key])
        {
            if (!isset($args[0]) || $args[0]{0} == '-')
                throw new SConsoleException("Parameter missing for the $key option");
            else
                $value = array_shift($args);
        }
        else $value = true;
        
        return array($key, $value);
    }
}

?>
