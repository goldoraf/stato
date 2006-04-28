<?php

/**
 * Form helpers
 * 
 * Set of functions for working with forms related to objects assigned to the 
 * template. All helpers are tailored for accessing an attribute (identified 
 * by <var>$method</var>) on an object assigned to the template (identified by
 * <var>$object</var>). <var>$objectName</var> is used for generation of "id" and
 * "name" attributes. Additional attributes for the tag can be passed as an array
 * with <var>$options</var>. Some helpers have specific options too.
 * 
 * @package Stato
 * @subpackage view
 */
/**
 * Returns an input tag of the "text" type.
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

/**
 * Returns an input tag of the "file" type, which won't have a default value.
 */
function file_field($objectName, $method, $object, $options = array())
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return file_field_tag($name, $options);
}

/**
 * Returns an input tag of the "password" type.
 */
function password_field($objectName, $method, $object, $options = array())
{
    $options = array_merge(array('size' => 30), $options);
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return password_field_tag($name, $value, $options);
}

/**
 * Returns an input tag of the "hidden" type.
 */
function hidden_field($objectName, $method, $object, $options = array())
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return hidden_field_tag($name, $value, $options);
}

/**
 * Returns a textarea opening and closing tag set.
 * 
 * Example :
 * <code>text_area('post', 'body', $this->post, array('cols' => 40, 'rows' => 10));
 *      <textarea id="post_body" name="post[body]" cols="40" rows="10">
 *          .....
 *      </textarea></code>  
 */
function text_area($objectName, $method, $object, $options = array())
{
    $options = array_merge(array('cols' => 40, 'rows' => 20), $options);
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    return text_area_tag($name, html_escape($value), $options);
}

/**
 * Returns a checkbox tag.
 * 
 * The checkbox will be checked if <var>$method</var> returns true. The <var>$checkedValue</var> 
 * defaults to 1 while the default <var>$uncheckedValue</var> is set to 0 because this
 * value will be typecasted to a boolean by the Active Record. Due to the fact that 
 * usually unchecked checkboxes don’t post anything, a hidden value is added with 
 * the same name as the checkbox. 
 * Example :
 * <code>check_box('post', 'private', $this->post);
 *      <input checked="checked" id="post_private" name="post[private]" type="checkbox" value="1" />
 *      <input name="post[private]" type="hidden" value="0" /></code>  
 */
function check_box($objectName, $method, $object, $options = array(), $checkedValue = '1', $uncheckedValue = '0')
{
    list($name, $value, $options) = default_options($objectName, $method, $object, $options);
    if ($value) $checked = True;
    else $checked = False;
    return check_box_tag($name, $checkedValue, $checked, $options)
    .tag('input', array('type' => 'hidden', 'name' => $name, 'value' => $uncheckedValue));
}

/**
 * Returns a radio button tag.
 * 
 * The radio button will be checked if the current value of <var>$method</var> is 
 * equal to <var>$tagValue</var>. 
 * Example :
 * <code>radio_button('post', 'title', $this->post, 'Hello World');
 *      <input id="post_title_hello_world" name="post[title]" type="radio" value="Hello World" /></code>  
 */
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
