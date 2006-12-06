<?php

class SConsoleException extends Exception {}

class SConsoleUtils
{
    public static function register_global_options($options_list)
    {
        $short_options = '';
        $long_options  = array();
        
        foreach ($options_list as $o)
        {
            $lower_o = str_replace('_', '-', strtolower($o));
            $short_options .= $lower_o.':';
            $long_options[] = $lower_o.'=';
        }
        
        $options = SConsoleUtils::read_options($short_options, $long_options);
        
        foreach ($options_list as $o)
        {
            $lower_o = str_replace('_', '-', strtolower($o));
            
            if (isset($_SERVER[$o])) $GLOBALS[$o] = $_SERVER[$o];
            if (($option = SConsoleUtils::get_option($lower_o, $options)) !== null) $GLOBALS[$o] = $option;
            
            if (!isset($GLOBALS[$o]))
                throw new SConsoleException($o.' is undefined !');
        }
    }
    
    public static function get_option($name, $options)
    {
        if (isset($options[$name])) return $options[$name];
        elseif (isset($options[$name{0}])) return $options[$name{0}];
        else return null;
    }
    
    public static function read_options($short_options, $long_options = null)
    {
        $args = self::read_arguments();
        array_shift($args);
        list($opt, ) = self::parse_options($args, $short_options, $long_options);
        return $opt;
    }
    
    /**
     * function adapted from PEAR::console_getopt by Andrei Zmievski
     * Parses the command-line options.
     *
     * The first parameter to this function should be the list of command-line
     * arguments without the leading reference to the running program.
     *
     * The second parameter is a string of allowed short options. Each of the
     * option letters can be followed by a colon ':' to specify that the option
     * requires an argument, or a double colon '::' to specify that the option
     * takes an optional argument.
     *
     * The third argument is an optional array of allowed long options. The
     * leading '--' should not be included in the option name. Options that
     * require an argument should be followed by '=', and options that take an
     * option argument should be followed by '=='.
     *
     * The return value is an array of two elements: the list of parsed
     * options and the list of non-option command-line arguments. Each entry in
     * the list of parsed options is a pair of elements - the first one
     * specifies the option, and the second one specifies the option argument,
     * if there was one.
     *
     * Long and short options can be mixed.
     *
     * Most of the semantics of this function are based on GNU getopt_long().
     *
     * @param array  $args           an array of command-line arguments
     * @param string $short_options  specifies the list of allowed short options
     * @param array  $long_options   specifies the list of allowed long options
     *
     * @return array two-element array containing the list of parsed options and
     * the non-option arguments
     *
     * @access public
     *
     */
    public static function parse_options($args, $short_options, $long_options = null)
    {
        if (empty($args)) return array(array(), array());
        
        $opts     = array();
        $non_opts = array();

        settype($args, 'array');

        if ($long_options) sort($long_options);

        reset($args);
        while (list($i, $arg) = each($args))
        {
            /* The special element '--' means explicit end of
               options. Treat the rest of the arguments as non-options
               and end the loop. */
            if ($arg == '--')
            {
                $non_opts = array_merge($non_opts, array_slice($args, $i + 1));
                break;
            }

            if ($arg{0} != '-' || (strlen($arg) > 1 && $arg{1} == '-' && !$long_options))
            {
                $non_opts = array_merge($non_opts, array_slice($args, $i));
                break;
            }
            elseif (strlen($arg) > 1 && $arg{1} == '-')
                self::parse_long_option(substr($arg, 2), $long_options, $opts, $args);
            else
                self::parse_short_option(substr($arg, 1), $short_options, $opts, $args);
        }
        
        return array($opts, $non_opts);
    }
    
    /**
    * function adapted from PEAR::console_getopt by Andrei Zmievski
    * Safely read the $argv PHP array across different PHP configurations.
    * Will take care on register_globals and register_argc_argv ini directives
    *
    * @return mixed the $argv PHP array
    */
    public static function read_arguments()
    {
        global $argv;
        if (!is_array($argv))
        {
            if (!@is_array($_SERVER['argv']))
            {
                if (!@is_array($globals['HTTP_SERVER_VARS']['argv']))
                    throw new SConsoleException("Could not read cmd args (register_argc_argv = Off ?)");
                
                return $globals['HTTP_SERVER_VARS']['argv'];
            }
            return $_SERVER['argv'];
        }
        return $argv;
    }
    
    private static function parse_short_option($arg, $short_options, &$opts, &$args)
    {
        for ($i = 0; $i < strlen($arg); $i++)
        {
            $opt = $arg{$i};
            $opt_arg = null;

            /* Try to find the short option in the specifier string. */
            if (($spec = strstr($short_options, $opt)) === false || $arg{$i} == ':')
                throw new SConsoleException("Unrecognized option -- $opt");

            if (strlen($spec) > 1 && $spec{1} == ':')
            {
                if (strlen($spec) > 2 && $spec{2} == ':')
                {
                    if ($i + 1 < strlen($arg))
                    {
                        /* Option takes an optional argument. Use the remainder of
                           the arg string if there is anything left. */
                        $opts[] = array($opt, substr($arg, $i + 1));
                        break;
                    }
                }
                else
                {
                    /* Option requires an argument. Use the remainder of the arg
                       string if there is anything left. */
                    if ($i + 1 < strlen($arg))
                    {
                        $opts[] = array($opt,  substr($arg, $i + 1));
                        break;
                    }
                    else if (list(, $opt_arg) = each($args))
                        /* Else use the next argument. */;
                    else
                        throw new SConsoleException("Option requires an argument -- $opt");
                }
            }

            $opts[$opt] = $opt_arg;
        }
    }
    
    private static function parse_long_option($arg, $long_options, &$opts, &$args)
    {
        @list($opt, $opt_arg) = explode('=', $arg);
        $opt_len = strlen($opt);

        for ($i = 0; $i < count($long_options); $i++)
        {
            $long_opt  = $long_options[$i];
            $opt_start = substr($long_opt, 0, $opt_len);

            /* Option doesn't match. Go on to the next one. */
            if ($opt_start != $opt)
                continue;

            $opt_rest  = substr($long_opt, $opt_len);

            /* Check that the options uniquely matches one of the allowed
               options. */
            if ($opt_rest != '' && $opt{0} != '=' &&
                $i + 1 < count($long_options) &&
                $opt == substr($long_options[$i+1], 0, $opt_len)) {
                throw new SConsoleException("Option --$opt is ambiguous");
            }

            if (substr($long_opt, -1) == '=')
            {
                if (substr($long_opt, -2) != '==')
                {
                    /* Long option requires an argument.
                       Take the next argument if one wasn't specified. */;
                    if (!strlen($opt_arg) && !(list(, $opt_arg) = each($args)))
                        throw new SConsoleException("Option --$opt requires an argument");
                }
            }
            elseif ($opt_arg)
                throw new SConsoleException("Option --$opt doesn't allow an argument");

            $opts[$opt] = $opt_arg;
            return;
        }

        throw new SConsoleException("Unrecognized option --$opt");
    }
}

?>
