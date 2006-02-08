<?php

function form($objectName, $options=array())
{
    $entity = SContext::$response[$objectName];
    
    if (!isset($options['action']))
    {
        if ($entity->isNewRecord()) $options['action'] = 'create';
        else $options['action'] = 'update';
    }
    
    if (!isset($options['submit_value']))
        $options['submit_value'] = ucfirst($options['action']);
    
    $form = '<form id="" enctype="multipart/form-data" method="post" action="'
            .url_for(array('action' => $options['action'])).'">';
    
    if (!$entity->isNewRecord()) $form.= hidden_field($objectName, 'id');
    
    $fields = $entity->contentAttributes();
    $class  = get_class($entity);
    
    foreach($fields as $name)
    {
        $attr = $entity->attributes[$name];
        $label = ucfirst($attr->name);
        
        $form.= '<p><label for="'.strtolower($class).'_'.$attr->name.'">'
        .$label."</label>\n".input($objectName, $attr->name)."</p>\n";
    }
    
    if (isset($options['include'])) $form.= $options['include'];
    
    $form.= submit_tag($options['submit_value']);
    $form.= '</form>';
    
    return $form;
}

function input($objectName, $method, $options=array())
{
    $attr = SContext::$response[$objectName]->attributes[$method];
    
    switch($attr->type)
    {
        case 'string':
            $str = text_field($objectName, $method, $options);
            break;
        case 'text':
            $str = text_area($objectName, $method, $options);
            break;
        case 'date':
            $str = date_select($objectName, $method);
            break;
        case 'datetime':
            $str = date_time_select($objectName, $method);
            break;
        case 'integer':
            $str = text_field($objectName, $method, $options);
            break;
        case 'boolean':
            $str = select($objectName, $method, array('True', 'False'), array(), $options);
            break;
        default:
            $str = hidden_field($objectName, $method);
            break;
    }
    return $str;
}

function error_message_on($objectName, $method, $prependText='', $appendText='', $divClass='form-error')
{
    $errors = SContext::$response[$objectName]->errors;
    if (isset($errors[$method]))
        return "<div class=\"{$divClass}\">{$prependText}{$errors[$method]}{$appendText}</div>";
}

function error_message_for($objectName, $options=array())
{
    $errors = SContext::$response[$objectName]->errors;
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
        content_tag($headerTag, SContext::locale('ERR_VALID_FORM'))."<ul>{$list}</ul>", 
        $options);
    }
}

?>
