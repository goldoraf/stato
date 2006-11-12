<?php

function link_to($label, $url_options=array(), $html_options=array())
{
    if (is_array($url_options)) $url = url_for($url_options);
    else $url = html_escape($url_options);
    
    if (isset($html_options['confirm']))
    {
        $html_options['onclick'] = "input=confirm('{$html_options['confirm']}');return input;";
        unset($html_options['confirm']);
    }
    
    return content_tag('a', $label, array_merge(array('href' => $url), $html_options));
}

function link_to_unless_current($label, $url_options=array(), $html_options=array())
{
    return link_to_unless(is_current_page($url_options), $label, $url_options, $html_options);
}

function link_to_unless($condition, $label, $url_options=array(), $html_options=array())
{
    if ($condition) return $label;
    else return link_to($label, $url_options, $html_options);
}

function link_to_if($condition, $label, $url_options=array(), $html_options=array())
{
    return link_to_unless(!$condition, $label, $url_options, $html_options);
}

function button_to($label, $url_options=array(), $html_options=array())
{
    if (is_array($url_options)) $url = url_for($url_options);
    else $url = html_escape($url_options);
    
    if (isset($html_options['confirm']))
    {
        $html_options['onclick'] = "input=confirm('{$html_options['confirm']}');return input;";
        unset($html_options['confirm']);
    }
    
    $html_options = array_merge(array('type'=>'submit', 'value'=>$label), $html_options);
    
    return "<form method=\"post\" action=\"{$url}\" class=\"button-to\"><div>"
    .tag('input', $html_options)."</div></form>";
}

function url_for($options)
{
    return SUrlRewriter::url_for($options);
}

function is_current_page($options)
{
    return SUrlRewriter::is_current_page($options);
}

?>
