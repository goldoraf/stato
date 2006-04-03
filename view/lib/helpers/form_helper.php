<?php

function default_options($objectName, $method, $object, $options = array())
{
    if (isset($options['index']))
    {
        $index = $options['index'];
        unset($options['index']);
        $name = "{$objectName}[{$index}][{$method}]";
        $id = "{$objectName}_{$index}_{$method}";
    }
    else
    {
        $name = "{$objectName}[{$method}]";
        $id = "{$objectName}_{$method}";
    }
    $options = array_merge(array('id' => $id), $options);
    return array($name, $object->$method, $options);
}

function text_field($objectName, $method, $object, $options = array())
{
    $options = array_merge(array('size' => 30), $options);
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
    $options = array_merge(array('size' => 30), $options);
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
    $options = array_merge(array('cols' => 40, 'rows' => 20), $options);
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return text_area_tag($name, html_escape($value), $options);
}

function check_box($objectName, $method, $object, $options = array(), $checkedValue = '1', $uncheckedValue = '0')
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    if ($value) $checked = True;
    else $checked = False;
    return check_box_tag($name, $checkedValue, $checked, $options)
    .tag('input', array('type' => 'hidden', 'name' => $name, 'value' => $uncheckedValue));
}

function radio_button($objectName, $method, $object, $tagValue, $options = array())
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    $options['id'].= '_'.SInflection::wikify($tagValue);
    if ($value == $tagValue) $checked = True;
    else $checked = False;
    return radio_button_tag($name, $tagValue, $checked, $options);
}

?>
