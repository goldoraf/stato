<?php

function form_tag($url_options, $options=array())
{
    $html_options = array_merge(array('method'=>'post'), $options);
    if ($html_options['multipart'])
    {
        $html_options['enctype'] = "multipart/form-data";
        unset($html_options['multipart']);
    }
    $html_options['action'] = url_for($url_options);
    return tag('form', $html_options, True);
}

function start_form_tag($url_options, $options=array())
{
    return form_tag($url_options, $options);
}

function end_form_tag()
{
    return '</form>';
}

function text_field_tag($name, $value=Null, $options=array())
{
    return tag('input', array_merge(array('type' => 'text', 'name' => $name, 
                                          'id' => $name, 'value' => $value), $options));
}

function password_field_tag($name, $value=Null, $options=array())
{
    return text_field_tag($name, $value, array_merge($options, array('type' => 'password')));
}

function hidden_field_tag($name, $value=Null, $options=array())
{
    return text_field_tag($name, $value, array_merge($options, array('type' => 'hidden')));
}

function file_field_tag($name, $options=array())
{
    return text_field_tag($name, null, array_merge($options, array('type' => 'file')));
}

function text_area_tag($name, $content=Null, $options=array())
{
    if (isset($options['size']))
    {
        list($options['cols'], $options['rows']) = explode('x', $options['size']);
        unset($options['size']);
    }
    return content_tag('textarea', $content, array_merge(array('name' => $name, 'id' => $name), $options));
}

function submit_tag($value='Ok', $options=array())
{
    if (isset($options['disable_with']))
    {
        $options['onclick'] = "this.disabled=true;this.value='{$options['disable_with']}';"
                              ."this.form.submit();{$options['onclick']}";
        unset($options['disable_with']);
    }
    return tag('input', array_merge(array('type' => 'submit', 'name' => 'commit', 
                                          'value' => $value), $options));
}

function image_submit_tag($source, $options=array())
{
    return tag('input', array_merge(array('type' => 'image', 'src' => image_path($source)), $options));
}

function select_tag($name, $options_block='', $options=array())
{
    return content_tag('select', $options_block, array_merge(array('name'=>$name, 'id'=>$name), $options));
}

function check_box_tag($name, $value="1", $checked=false, $options=array())
{
    $html_options = array_merge(array('type'=>'checkbox', 'name'=>$name, 'id'=>$name, 'value'=>$value), $options);
    if ($checked) $html_options['checked'] = "checked";
    return tag('input', $html_options);
}

function radio_button_tag($name, $value, $checked=false, $options=array())
{
    $html_options = array_merge(array('type'=>'radio', 'name'=>$name, 'id'=>$name, 'value'=>$value), $options);
    if ($checked) $html_options['checked'] = "checked";
    return tag('input', $html_options);
}

?>
