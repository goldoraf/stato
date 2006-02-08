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

function text_field_tag($name, $id=Null, $value=Null, $options=array())
{
    if ($id == Null) $id = $name;
    return "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$value\" ".tag_options($options)."/>\n";
}

function password_field_tag($name, $id=Null, $value=Null, $options=array())
{
    if ($id == Null) $id = $name;
    return "<input type=\"password\" id=\"$id\" name=\"$name\" value=\"$value\" ".tag_options($options)."/>\n";
}

function hidden_field_tag($name, $id=Null, $value=Null, $options=array())
{
    if ($id == Null) $id = $name;
    return "<input type=\"hidden\" id=\"$id\" name=\"$name\" value=\"$value\" ".tag_options($options)."/>\n";
}

function file_field_tag($name, $id=Null, $options=array())
{
    if ($id == Null) $id = $name; // attention...
    return "<input type=\"file\" id=\"$id\" name=\"$name\" ".tag_options($options)."/>\n";
}

function text_area_tag($name, $id=Null, $content=Null, $options=array())
{
    if ($id == Null) $id = $name;
    return content_tag('textarea', $content, array_merge(array('name'=>$name, 'id'=>$id), $options));
}

function submit_tag($value='Ok', $options=array())
{
    return "<input type=\"submit\" name=\"submit\" value=\"$value\" ".tag_options($options)."/>\n";
}

function select_tag($name, $id=Null, $optionsBlock='', $options=array())
{
    if ($id == Null) $id = $name;
    return content_tag('select', $optionsBlock, array_merge(array('name'=>$name, 'id'=>$id), $options));
}

function check_box_tag($name, $id=Null, $value="1", $checked=false, $options=array())
{
    $htmlOptions = array_merge(array('type'=>'checkbox', 'name'=>$name, 'id'=>$id, 'value'=>$value), $options);
    if ($checked) $htmlOptions['checked'] = "checked";
    return tag('input', $htmlOptions);
}

function radio_button_tag($name, $id, $value, $checked=false, $options=array())
{
    $htmlOptions = array_merge(array('type'=>'radio', 'name'=>$name, 'id'=>$id, 'value'=>$value), $options);
    if ($checked) $htmlOptions['checked'] = "checked";
    return tag('input', $htmlOptions);
}

?>
