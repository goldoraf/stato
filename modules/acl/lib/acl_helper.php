<?php

if (!function_exists('__'))
{
    function __($str, $args = array(null))
    {
        array_unshift($args, $str);
        return call_user_func_array('sprintf', $args);
    }
}

function link_if_authorized($label, $user, $url_options=array(), $html_options=array())
{
    $result = (array_delete('show_text', $html_options)) ? $label : '';
    $wrap_tag = array_delete('wrap_in', $html_options);
    
    if (is_authorized($user, $url_options))
    {
        $result = link_to($label, $url_options, $html_options);
        if ($wrap_tag !== null) $result = content_tag($wrap_tag, $result);
    }
    
    return $result;
}

function is_authorized($user, $options)
{
    $action     = (isset($options['action'])) ? $options['action'] : 'index';
    $controller = (isset($options['controller'])) ? $options['controller'] : SUrlRewriter::current_controller();
    
    if (!$user)
    {
        SLogger::get_instance()->debug("checking guest authorisation for {$controller}/{$action}");
        return AclEngine::is_guest_user_authorized($controller, $action);
    }
    else
    {
        SLogger::get_instance()->debug("checking user #{$user->id} authorisation for {$controller}/{$action}");
        return AclEngine::is_authorized($user, $controller, $action);
    }
}

function array_delete($key, $array)
{
    $value = (isset($array[$key])) ? $array[$key] : null;
    unset($array[$key]);
    return $value;
}

?>
