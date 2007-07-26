<?php

function content_tag($name, $content, $options = array())
{
    return "<$name".tag_options($options).">$content</$name>";
}

function tag($name, $options = array(), $open = False)
{
    return "<$name".tag_options($options).($open ? ">" : " />");
}

function tag_options($options = array())
{
    if (count($options) == 0) return;
    $set = array();
    foreach($options as $key => $value)
    {
        if ($value !== null && $value !== false)
        {
            if ($value === true) $set[] = $key.'="'.$key.'"';
            else $set[] = $key.'="'.$value.'"';
        }
    }
    return ' '.implode(" ", $set);
}

function cdata_section($content)
{
    return "<![CDATA[{$content}]]>";
}

?>
