<?php

function form($objectName, $object, $options=array())
{
    if (!isset($options['action']))
    {
        if ($object->isNewRecord()) $options['action'] = 'create';
        else $options['action'] = 'update';
    }
    
    if (!isset($options['submit_value']))
        $options['submit_value'] = ucfirst($options['action']);
    
    if (isset($options['multipart']) && $options['multipart'] === true)
        $form = form_tag(array('action' => $options['action']), array('multipart' => true));
    else
        $form = form_tag(array('action' => $options['action']));
    
    if (!$object->isNewRecord()) $form.= hidden_field($objectName, 'id', $object);
    
    $fields = $object->contentAttributes();
    
    foreach($fields as $attr)
    {
        $form.= '<p><label for="'.$objectName.'_'.$attr->name.'">'
        .SInflection::humanize($attr->name)."</label>\n"
        .input($objectName, $attr->name, $object)."</p>\n";
    }
    
    if (isset($options['include'])) $form.= $options['include'];
    
    $form.= submit_tag($options['submit_value']);
    $form.= end_form_tag();
    
    return $form;
}

function input($objectName, $method, $object, $options=array())
{
    $attr = $object->getAttribute($method);
    
    switch($attr->type)
    {
        case 'string':
            $str = text_field($objectName, $method, $object, $options);
            break;
        case 'text':
            $str = text_area($objectName, $method, $object, $options);
            break;
        case 'date':
            $str = date_select($objectName, $method, $object);
            break;
        case 'datetime':
            $str = date_time_select($objectName, $method, $object);
            break;
        case 'integer':
            $str = text_field($objectName, $method, $object, $options);
            break;
        case 'boolean':
            $str = check_box($objectName, $method, $object, $options);
            break;
        default:
            $str = hidden_field($objectName, $method, $object);
            break;
    }
    
    return error_wrapping($str, isset($object->errors[$method]));
}

function error_wrapping($tag, $hasError)
{
    return $hasError ? "<div class=\"field-with-errors\">$tag</div>" : $tag;
}

function error_message_on($method, $object, $prependText='', $appendText='', $divClass='form-error')
{
    if (isset($object->errors[$method]))
        return "<div class=\"{$divClass}\">{$prependText}{$errors[$method]}{$appendText}</div>";
}

function error_message_for($objectName, $object, $options=array())
{
    $errors = $object->errors;
    if (!empty($errors))
    {
        $headerTag = 'h2';
        if (isset($options['header_tag']))
        {
            $headerTag = $options['header_tag'];
            unset($options['header_tag']);
        }
        
        if (!isset($options['id']))    $options['id'] = 'form-errors';
        if (!isset($options['class'])) $options['class'] = 'form-errors';
        
        $list = '';
        foreach($errors as $field => $error)
            $list.= '<li>'.link_to_function($error, "Field.focus('{$objectName}_{$field}')").'</li>';
            
        return content_tag('div', 
        content_tag($headerTag, SLocale::translate('ERR_VALID_FORM'))."<ul>{$list}</ul>", 
        $options);
    }
}

?>
