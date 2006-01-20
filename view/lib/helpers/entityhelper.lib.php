<?php

/**
 * Form Helper
 *
 * options possibles :
 * - action
 * - include : pour ajouter un bloc html quelconque au form
 * 
 **/
function form($objectName, $options=array())
{
    $entity = Context::$response[$objectName];
    
    if (!isset($options['action']))
    {
        if ($entity->isNewRecord()) $options['action'] = 'add';
        else $options['action'] = 'edit';
    }
    $form = '<form id="" enctype="multipart/form-data" method="post" action="'
            .url_for(array('action' => $options['action'])).'">';
    
    if (!$entity->isNewRecord()) $form.= hidden_field($objectName, 'id');
    
    $fields = $entity->contentAttributes();
    $class  = get_class($entity);
    
    /*if (is_subclass_of($class, 'UserDefinedIdEntity'))
    {
        $form.= '<p><label for="'.strtolower($class).'_'.$entity->identityField.'">'
        .ucfirst($entity->identityField).'</label>'.text_field($objectName, $entity->identityField).'</p>';
    }*/
    
    foreach($fields as $name)
    {
        $attr = $entity->attributes[$name];
        $label = ucfirst($attr->name);
        
        $form.= '<p><label for="'.strtolower($class).'_'.$attr->name.'">'
        .$label."</label>\n".input($objectName, $attr->name)."</p>\n";
    }
    
    /*if (is_subclass_of($class, 'FileEntity'))
    {
        $form.= '<p><label for="'.strtolower($class).'_file">File</label>'
        .file_field($objectName, 'file').'</p>';
    }*/
    
    if (isset($options['include'])) $form.= $options['include'];
    
    $form.= submit_tag('Enregistrer');
    $form.= '</form>';
    
    return $form;
}

function input($objectName, $method)
{
    $attr = Context::$response[$objectName]->attributes[$method];
    
    if (isset($attr->options['choices']))
    {
        if (!isset($attr->options['required']) || $attr->options['required'] == false)
            return select($objectName, $method, $attr->options['choices'], array('include_blank' => True));
        elseif (count($attr->options['choices']) <= 3)
        {
            $str = '';
            foreach($attr->options['choices'] as $choice) $str.= radio_button($objectName, $method, $choice).$choice;
            return $str;
        }
        else
            return select($objectName, $method, $attr->options['choices']);
    }
    
    switch($attr->type)
    {
        case 'string':
            $str = text_field($objectName, $method);
            break;
        case 'text':
            $str = text_area($objectName, $method);
            break;
        case 'date':
            $str = date_select($objectName, $method);
            break;
        case 'datetime':
            $str = date_time_select($objectName, $method);
            break;
        case 'integer':
            $str = text_field($objectName, $method);
            break;
        case 'boolean':
            $str = select($objectName, $method, array('True', 'False'));
            break;
        default:
            $str = hidden_field($objectName, $method);
            break;
    }
    return $str;
}

function error_message_on($objectName, $method, $prependText='', $appendText='', $divClass='field-error')
{
    $errors = Context::$response[$objectName]->errors;
    if (isset($errors[$method]))
        return "<div class=\"{$divClass}\">{$prependText}{$errors[$method]}{$appendText}</div>";
}

function error_message_for($objectName, $divClass='form-error')
{
    $errors = Context::$response[$objectName]->errors;
    if (!empty($errors))
    {
        $js = js_tag("function setfocus(objectid) { "
        ."if(document.getElementById(objectid)) { "
        ."document.getElementById(objectid).focus(); }}");
        
        $list = '';
        foreach($errors as $field => $error)
        {
            $list.= "<li><a href=\"#\" onclick=\"setfocus('{$objectName}_{$field}')\">"
            ."{$error}</a></li>";
        }
        
        return "{$js}<div class=\"{$divClass}\"><p>".Context::locale('ERR_VALID_FORM')
        ."</p><ul>{$list}</ul></div>";
    }
}

/**
 * Table Helper
 *
 * options possibles :
 * - attributes : array de type attribute => label (ex : array('id' => 'ID'))
 * 
 **/
function table($data, $actions=array(), $options=array())
{
    if (empty($actions))
    {
        $actions = array('view', 'edit', 'delete');
    }
    
    $count = count($data);
    if ($count == 0)
    {
        $str = "<p>Aucune donnée disponible.</p>";
    }
    else
    {
        $str = '';
        $entity = $data[0];
        $attributes = $entity->getAttributes();
        
        for ($i=0; $i<$count; $i++)
        {
            $entity = $data[$i];
            // ajout du header
            if ($i == 0)
            {
                $str.= table_header($attributes);
            }
            // construction d'une ligne
            $str.= '<tr>';
            foreach($attributes as $attr)
            {
                $value = $entity->$attr;
                if (is_object($value))
                {
                    $value = $value->__toString();
                }
                if (strlen($value) > 40)
                {
                    $value = substr($value, 0, 40).'...';
                }
                $str.= '<td>'.$value.'</td>';
            }
            // ajout des actions
            if (!empty($actions))
            {
                $id = $entity->id();
                $str.= '<td>';
                foreach ($actions as $action)
                {
                    $str.= link_to($action, array('action' => $action, 'id' => $id)).'&nbsp;';
                }
                $str.= '</td>';
            }
            $str.= '</tr>';
        }
    }
    return '<table>'.$str.'</table>';
}

function table_header($keys)
{
    $str = '<tr>';
    foreach($keys as $key)
    {
        $str.= '<th>'.ucfirst($key).'</th>';
    }
    $str.= '</tr>';
    return $str;
}

?>
