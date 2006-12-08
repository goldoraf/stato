<?php

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

function error_message_on($method, $object, $prepend_text='', $append_text='', $div_class='form-error')
{
    if (isset($object->errors[$method]))
        return "<div class=\"{$div_class}\">{$prepend_text}{$object->errors[$method]}{$append_text}</div>";
}

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
