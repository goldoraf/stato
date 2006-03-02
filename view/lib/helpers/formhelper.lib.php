<?php

function default_options($objectName, $method, $object)
{
    return array("{$objectName}_{$method}", "{$objectName}[{$method}]", $object->$method);
}

function text_field($objectName, $method, $object, $options = array())
{
    list($id, $name, $value) = default_options($objectName, $method, $object);
    return text_field_tag($name, $id, $value, $options);
}

function file_field($objectName, $method, $object, $options = array())
{
    list($id, $name, $value) = default_options($objectName, $method, $object);
    return file_field_tag($name, $id, $options);
}

function password_field($objectName, $method, $object, $options = array())
{
    list($id, $name, $value) = default_options($objectName, $method, $object);
    return password_field_tag($name, $id, $value, $options);
}

function hidden_field($objectName, $method, $object, $options = array())
{
    list($id, $name, $value) = default_options($objectName, $method, $object);
    return hidden_field_tag($name, $id, $value, $options);
}

function text_area($objectName, $method, $object, $options = array())
{
    list($id, $name, $value) = default_options($objectName, $method, $object);
    return text_area_tag($name, $id, $value, $options);
}

function check_box($objectName, $method, $object, $options = array(), $checkedValue = '1', $uncheckedValue = '0')
{
    list($id, $name, $value) = default_options($objectName, $method, $object);
    if ($value) $checked = True;
    else $checked = False;
    return hidden_field_tag($name, Null, $uncheckedValue)
    .check_box_tag($name, $id, $checkedValue, $checked, $options);
}

function radio_button($objectName, $method, $object, $tagValue, $options = array())
{
    list($id, $name, $value) = default_options($objectName, $method, $object);
    if ($value == $tagValue) $checked = True;
    else $checked = False;
    return radio_button_tag($name, $id, $tagValue, $checked, $options);
}

?>
