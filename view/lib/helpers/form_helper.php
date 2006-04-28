<?php

/**
 * Form helpers
 * 
 * Set of functions for working with forms related to objects assigned to the 
 * template. 
 * 
 * @package Stato
 * @subpackage view
 */
/**
 * Returns an input tag of the "text" type for accessing an attribute (identified 
 * by <var>$method</var>) on an object assigned to the template (identified by
 * <var>$object</var>). <var>$objectName</var> is used for generation of "id" and
 * "name" attributes. Additional attributes for the tag can be passed as an array
 * with <var>$options</var>.
 *  
 * Example (<var>$this->post->title</var> returns "PHP for ever") :
 * <code>text_field('post', 'title', $this->post, array('size' => 35));
 *      <input id="post_title" name="post[title]" size="35" type="text" value="PHP for ever" /></code>
 */
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

?>
