<?php

/**
 * Form helpers
 * 
 * Set of functions for working with forms related to objects assigned to the 
 * template. All helpers are tailored for accessing an attribute (identified 
 * by <var>$method</var>) on an object assigned to the template (identified by
 * <var>$object</var>). <var>$object_name</var> is used for generation of "id" and
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
function text_field($object_name, $method, $object, $options = array())
{
    $options = array_merge(array('size' => 30), $options);
    list($name, $value, $options) = default_options($object_name, $method, $object, $options);
    return text_field_tag($name, $value, $options);
}

/**
 * Returns an input tag of the "file" type, which won't have a default value.
 */
function file_field($object_name, $method, $object, $options = array())
{
    list($name, $value, $options) = default_options($object_name, $method, $object, $options);
    return file_field_tag($name, $options);
}

/**
 * Returns an input tag of the "password" type.
 */
function password_field($object_name, $method, $object, $options = array())
{
    $options = array_merge(array('size' => 30), $options);
    list($name, $value, $options) = default_options($object_name, $method, $object, $options);
    return password_field_tag($name, $value, $options);
}

/**
 * Returns an input tag of the "hidden" type.
 */
function hidden_field($object_name, $method, $object, $options = array())
{
    list($name, $value, $options) = default_options($object_name, $method, $object, $options);
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
function text_area($object_name, $method, $object, $options = array())
{
    $options = array_merge(array('cols' => 40, 'rows' => 20), $options);
    list($name, $value, $options) = default_options($object_name, $method, $object, $options);
    return text_area_tag($name, html_escape($value), $options);
}

/**
 * Returns a checkbox tag.
 * 
 * By default, the checkbox will be checked if <var>$method</var> returns true. 
 * The <var>$checked_value</var> defaults to 1 while the default <var>$unchecked_value</var> 
 * is set to 0 because this value will be typecasted to a boolean by the Active Record. 
 * But if you set <var>$boolean</var> option to false, the checkbox will be checked 
 * only if <var>$method</var> returns <var>$checked_value</var>. Due to the fact that 
 * usually unchecked checkboxes don’t post anything, a hidden value is added with 
 * the same name as the checkbox. 
 * Example :
 * <code>check_box('post', 'private', $this->post);
 *      <input name="post[private]" type="hidden" value="0" /></code> 
 *      <input checked="checked" id="post_private" name="post[private]" type="checkbox" value="1" />
 *       
 */
function check_box($object_name, $method, $object, $options = array(), $checked_value = '1', $unchecked_value = '0', $boolean = true)
{
    list($name, $value, $options) = default_options($object_name, $method, $object, $options);
    if ($boolean && $value) $checked = true;
    elseif ($value == $checked_value) $checked = true;
    else $checked = false;
    return tag('input', array('type' => 'hidden', 'name' => $name, 'value' => $unchecked_value))
    .check_box_tag($name, $checked_value, $checked, $options);
}

/**
 * Returns a radio button tag.
 * 
 * The radio button will be checked if the current value of <var>$method</var> is 
 * equal to <var>$tag_value</var>. 
 * Example :
 * <code>radio_button('post', 'title', $this->post, 'Hello World');
 *      <input id="post_title_hello_world" name="post[title]" type="radio" value="Hello World" /></code>  
 */
function radio_button($object_name, $method, $object, $tag_value, $options = array())
{
    list($name, $value, $options) = default_options($object_name, $method, $object, $options);
    $options['id'].= '_'.SInflection::wikify($tag_value);
    if ($value == $tag_value) $checked = true;
    else $checked = false;
    return radio_button_tag($name, $tag_value, $checked, $options);
}

function default_options($object_name, $method, $object, $options = array())
{
    if (isset($options['index']))
    {
        $index = $options['index'];
        unset($options['index']);
        $name = "{$object_name}[{$index}][{$method}]";
        $id = "{$object_name}_{$index}_{$method}";
    }
    else
    {
        $name = "{$object_name}[{$method}]";
        $id = "{$object_name}_{$method}";
    }
    if (isset($options['error_class']))
    {
        if (isset($object->errors[$method]))
        {
            if (isset($options['class'])) $options['class'].= ' '.$options['error_class'];
            else $options['class'] = $options['error_class'];
        }
        unset($options['error_class']);
    }
    $options = array_merge(array('id' => $id), $options);
    return array($name, $object->$method, $options);
}

?>
