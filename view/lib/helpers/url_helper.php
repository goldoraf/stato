<?php

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

function button_to($label, $urlOptions=array(), $htmlOptions=array())
{
    if (is_array($urlOptions)) $url = url_for($urlOptions);
    else $url = html_escape($urlOptions);
    
    if (isset($htmlOptions['confirm']))
    {
        $htmlOptions['onclick'] = "input=confirm('{$htmlOptions['confirm']}');return input;";
        unset($htmlOptions['confirm']);
    }
    
    $htmlOptions = array_merge(array('type'=>'submit', 'value'=>$label), $htmlOptions);
    
    return "<form method=\"post\" action=\"{$url}\" class=\"button-to\"><div>"
    .tag('input', $htmlOptions)."</div></form>";
}

function url_for($options)
{
    return SUrlRewriter::urlFor($options);
}

?>
