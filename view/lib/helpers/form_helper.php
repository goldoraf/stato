<?php

function default_options($objectName, $method, $object, $options = array())
{
    $options = array_merge(array('id' => "{$objectName}_{$method}"), $options);
    return array("{$objectName}[{$method}]", $object->$method, $options);
}

function text_field($objectName, $method, $object, $options = array())
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return text_field_tag($name, $value, $options);
}

function file_field($objectName, $method, $object, $options = array())
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return file_field_tag($name, $options);
}

function password_field($objectName, $method, $object, $options = array())
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return password_field_tag($name, $value, $options);
}

function hidden_field($objectName, $method, $object, $options = array())
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return hidden_field_tag($name, $value, $options);
}

function text_area($objectName, $method, $object, $options = array())
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return text_area_tag($name, $value, $options);
}

function check_box($objectName, $method, $object, $options = array(), $checkedValue = '1', $uncheckedValue = '0')
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    if ($value) $checked = True;
    else $checked = False;
    return hidden_field_tag($name, Null, $uncheckedValue)
    .check_box_tag($name, $checkedValue, $checked, $options);
}

function radio_button($objectName, $method, $object, $tagValue, $options = array())
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    if ($value == $tagValue) $checked = True;
    else $checked = False;
    return radio_button_tag($name, $tagValue, $checked, $options);
}

?>
