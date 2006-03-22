<?php

function form_tag($urlOptions, $options=array())
{
    $htmlOptions = array_merge(array('method'=>'post'), $options);
    if ($htmlOptions['multipart'])
    {
        $htmlOptions['enctype'] = "multipart/form-data";
        unset($htmlOptions['multipart']);
    }
    $htmlOptions['action'] = url_for($urlOptions);
    return tag('form', $htmlOptions, True);
}

function end_form_tag()
{
    return '</form>';
}

function text_field_tag($name, $value=Null, $options=array())
{
    return "<input type=\"text\" name=\"$name\" value=\"$value\" ".tag_options($options)."/>\n";
}

function password_field_tag($name, $value=Null, $options=array())
{
    return "<input type=\"password\" name=\"$name\" value=\"$value\" ".tag_options($options)."/>\n";
}

function hidden_field_tag($name, $value=Null, $options=array())
{
    return "<input type=\"hidden\" name=\"$name\" value=\"$value\" ".tag_options($options)."/>\n";
}

function file_field_tag($name, $options=array())
{
    return "<input type=\"file\" name=\"$name\" ".tag_options($options)."/>\n";
}

function text_area_tag($name, $content=Null, $options=array())
{
    return content_tag('textarea', $content, array_merge(array('name'=>$name), $options));
}

function submit_tag($value='Ok', $options=array())
{
    return "<input type=\"submit\" name=\"commit\" value=\"$value\" ".tag_options($options)."/>\n";
}

function image_submit_tag($source, $options=array())
{
    return "<input type=\"image\" src=\"".image_path($source)."\" ".tag_options($options)."/>\n";
}

function select_tag($name, $optionsBlock='', $options=array())
{
    return content_tag('select', $optionsBlock, array_merge(array('name'=>$name), $options));
}

function check_box_tag($name, $value="1", $checked=false, $options=array())
{
    $htmlOptions = array_merge(array('type'=>'checkbox', 'name'=>$name, 'value'=>$value), $options);
    if ($checked) $htmlOptions['checked'] = "checked";
    return tag('input', $htmlOptions);
}

function radio_button_tag($name, $value, $checked=false, $options=array())
{
    $htmlOptions = array_merge(array('type'=>'radio', 'name'=>$name, 'value'=>$value), $options);
    if ($checked) $htmlOptions['checked'] = "checked";
    return tag('input', $htmlOptions);
}

?>
