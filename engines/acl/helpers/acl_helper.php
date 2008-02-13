<?php

if (!function_exists('__'))
{
    function __($str, $args = array(null))
    {
        array_unshift($args, $str);
        return call_user_func_array('sprintf', $args);
    }
}

?>