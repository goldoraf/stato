<?php

/**
 * Active record helpers
 * 
 * Makes it easier to create forms for records kept in instance variables.
 * 
 * @package Stato
 * @subpackage view
 */
/**
 * Returns an entire form with input tags and everything for a specified ActiveRecord 
 * object. 
 * 
 * Example (<var>$post</var> is a new record that has a title using VARCHAR and a body using TEXT):
 * <code>
 * form('post', $post);
 * // could generate :
 * <form method="post" action="/posts/create">
 *   <p>
 *     <label for="post_title">Title</label>
 *     <input type="text" id="post_title" name="post[title]" value="" />
 *   </p>
 *   <p>
 *     <label for="post_body">Body</label>
 *     <textarea name="post[body]" id="post_body"></textarea>
 *   </p>
 *   <input type="submit" name="submit" value="Create" />
 * </form>
 * </code>
 * 
 * It’s possible to specialize the form by providing <var>action</var>, <var>submit_value</var>
 * and <var>include</var> HTML block.
 * 
 * Example :
 * <code>
 * form('product', $product, array('action' => 'register', 'submit_value' => 'Save',
                                   'include' => collection_select('department', 'id', $this->departments, 'id', 'name'));
 * </code>        
 */
function form($object_name, $object, $options=array())
{
    if (!isset($options['action']))
    {
        if ($object->is_new_record()) $options['action'] = 'create';
        else $options['action'] = 'update';
    }
    
    if (!isset($options['submit_value']))
        $options['submit_value'] = ucfirst($options['action']);
    
    if (isset($options['multipart']) && $options['multipart'] === true)
        $form = form_tag(array('action' => $options['action']), array('multipart' => true));
    else
        $form = form_tag(array('action' => $options['action']));
    
    if (!$object->is_new_record()) $form.= hidden_field($object_name, 'id', $object);
    
    $fields = $object->content_attributes();
    
    foreach($fields as $attr)
    {
        $form.= '<p><label for="'.$object_name.'_'.$attr->name.'">'
        .SInflection::humanize($attr->name)."</label>\n"
        .input($object_name, $attr->name, $object)."</p>\n";
    }
    
    if (isset($options['include'])) $form.= $options['include'];
    
    $form.= submit_tag($options['submit_value']);
    $form.= end_form_tag();
    
    return $form;
}

/**
 * Returns a default input tag for the type of object returned by the method. 
 * 
 * Example (title is a VARCHAR column and holds "Hello World"):
 * <code>
 * input('post', $post, 'title');
 * // could generate :
 * <input type="text" id="post_title" name="post[title]" value="Hello World !" />
 * </code>   
 */
function input($object_name, $method, $object, $options=array())
{
    $fields = $object->content_attributes();
    $attr = $fields[$method];
    
    switch($attr->type)
    {
        case 'string':
            $str = text_field($object_name, $method, $object, $options);
            break;
        case 'text':
            $str = text_area($object_name, $method, $object, $options);
            break;
        case 'date':
            $str = date_select($object_name, $method, $object);
            break;
        case 'datetime':
            $str = date_time_select($object_name, $method, $object);
            break;
        case 'integer':
            $str = text_field($object_name, $method, $object, $options);
            break;
        case 'float':
            $str = text_field($object_name, $method, $object, $options);
            break;
        case 'boolean':
            $str = check_box($object_name, $method, $object, $options);
            break;
        default:
            $str = hidden_field($object_name, $method, $object);
            break;
    }
    
    return error_wrapping($str, isset($object->errors[$method]));
}

function error_wrapping($tag, $has_error)
{
    return $has_error ? "<div class=\"field-with-errors\">$tag</div>" : $tag;
}

/**
 * Returns a string containing the error message attached to the <var>$method</var>
 * on the <var>$object</var>, if one exists. This error message is wrapped in a 
 * DIV tag, which can be specialized to include both a prepend_text and append_text
 * to properly introduce the error and a css_class to style it accordingly. 
 * 
 * Examples ($post has an error message "is required" on the title attribute):
 * 
 * <code>
 * error_message_on('title', $post);
 * // could generate :
 * <div class="form-error">is required</div>
 * error_message_on('title', $post, 'The Title field', '(you fool !)', 'input-error');
 * // could generate :
 * <div class="input-error">The Title field is required (you fool !)</div>
 * </code> 
 */
function error_message_on($method, $object, $prepend_text='', $append_text='', $div_class='form-error')
{
    if (isset($object->errors[$method]))
        return "<div class=\"{$div_class}\">{$prepend_text}{$object->errors[$method]}{$append_text}</div>";
}

/**
 * Returns a string with a div containing all the error messages for the <var>$object</var> 
 * whose form elements are prefixed by <var>$object_name</var>. 
 * This div can be tailored by the following options:
 *  - header_tag - Used for the header of the error div (default: h2)
 *  - id - The id of the error div (default: form-errors)
 *  - class - The class of the error div (default: form-errors)
 */
function error_message_for($object_name, $object, $options=array())
{
    $errors = $object->errors;
    if (!empty($errors))
    {
        $header_tag = 'h2';
        if (isset($options['header_tag']))
        {
            $header_tag = $options['header_tag'];
            unset($options['header_tag']);
        }
        
        if (!isset($options['id']))    $options['id'] = 'form-errors';
        if (!isset($options['class'])) $options['class'] = 'form-errors';
        
        $list = '';
        foreach($errors as $field => $error)
            $list.= '<li>'.link_to_function($error, "Field.focus('{$object_name}_{$field}')").'</li>';
            
        return content_tag('div', 
        content_tag($header_tag, SLocale::translate('ERR_VALID_FORM'))."<ul>{$list}</ul>", 
        $options);
    }
}

?>
