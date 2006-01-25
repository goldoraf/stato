<?php

/**
 * UriHelper
 * 
 * @package 
 * @author goldoraf
 * @copyright Copyright (c) 2005
 * @version 0.1
 * @access public
 **/
function link_to($label, $urlOptions=array(), $htmlOptions=array())
{
    if (is_array($urlOptions)) $url = url_for($urlOptions);
    else $url = html_escape($urlOptions);
    
    if (isset($htmlOptions['confirm']))
    {
        $htmlOptions['onclick'] = "input=confirm('{$htmlOptions['confirm']}');return input;";
        unset($htmlOptions['confirm']);
    }
    
    return content_tag('a', $label, array_merge(array('href' => $url), $htmlOptions));
}

function url_for($options)
{
    $req = Context::$request;
    if (!isset($options['action']))     $options['action'] = $req->action;
    if (!isset($options['controller'])) $options['controller'] = $req->controller;
    if (!isset($options['module']))     $options['module'] = $req->module;
    
    return Routes::rewriteUrl($options);
}

?>
