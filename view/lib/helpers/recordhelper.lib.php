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
    
    $form = '<form id="" enctype="multipart/form-data" method="post" action="'
            .url_for(array('action' => $options['action'])).'">';
    
    if (!$object->isNewRecord()) $form.= hidden_field($objectName, 'id', $object);
    
    $fields = $object->contentAttributes();
    $class  = get_class($object);
    
    foreach($fields as $name)
    {
        $attr = $object->attributes[$name];
        $label = ucfirst($attr->name);
        
        $form.= '<p><label for="'.strtolower($class).'_'.$attr->name.'">'
        .$label."</label>\n".input($objectName, $attr->name, $object)."</p>\n";
    }
    
    if (isset($options['include'])) $form.= $options['include'];
    
    $form.= submit_tag($options['submit_value']);
    $form.= '</form>';
    
    return $form;
}

function input($objectName, $method, $object, $options=array())
{
    $attr = $object->attributes[$method];
    
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
            $str = select($objectName, $method, $object, array('True', 'False'), array(), $options);
            break;
        default:
            $str = hidden_field($objectName, $method, $object);
            break;
    }
    return $str;
}

function error_message_on($method, $object, $prependText='', $appendText='', $divClass='form-error')
{
    $errors = $object->errors;
    if (isset($errors[$method]))
        return "<div class=\"{$divClass}\">{$prependText}{$errors[$method]}{$appendText}</div>";
}

function error_message_for($object, $options=array())
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
