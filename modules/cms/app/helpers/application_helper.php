<?php

function config_value($name)
{
    return Configuration::value($name);
}

function css_ie_fix()
{
    return "<!--[if lt IE 7]>\n".stylesheet_link_tag('ie_fix')."<![endif]-->\n";
}

function breadcrumbs($page, $separator = '&nbsp;>&nbsp;')
{
    
}

?>
