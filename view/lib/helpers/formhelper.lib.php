<?php

/**
 * FormHelper
 * 
 * @package 
 * @author goldoraf
 * @copyright Copyright (c) 2005
 * @version 0.1
 * @access public
 **/
function default_options($object, $method)
{
    $entity = Context::$response[$object];
    return array("{$object}_{$method}", "{$object}[{$method}]", $entity->$method);
}

function text_field($object, $method, $options = array())
{
    list($id, $name, $value) = default_options($object, $method);
    return text_field_tag($name, $id, $value, $options);
}

function file_field($object, $method, $options = array())
{
    list($id, $name, $value) = default_options($object, $method);
    return file_field_tag($name, $id, $options);
}

function password_field($object, $method, $options = array())
{
    list($id, $name, $value) = default_options($object, $method);
    return password_field_tag($name, $id, $value, $options);
}

function hidden_field($object, $method, $options = array())
{
    list($id, $name, $value) = default_options($object, $method);
    return hidden_field_tag($name, $id, $value, $options);
}

function text_area($object, $method, $options = array())
{
    list($id, $name, $value) = default_options($object, $method);
    return text_area_tag($name, $id, $value, $options);
}

function check_box($object, $method, $options = array(), $checkedValue = '1', $uncheckedValue = '0')
{
    list($id, $name, $value) = default_options($object, $method);
    if ($value) $checked = True;
    else $checked = False;
    return hidden_field_tag($name, Null, $uncheckedValue)
    .check_box_tag($name, $id, $checkedValue, $checked, $options);
}

function radio_button($object, $method, $tagValue, $options = array())
{
    list($id, $name, $value) = default_options($object, $method);
    if ($value == $tagValue) $checked = True;
    else $checked = False;
    return radio_button_tag($name, $id, $tagValue, $checked, $options);
}

?>
