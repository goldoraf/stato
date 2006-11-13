<?php

function javascript_tag($code)
{
    return '<script type="text/javascript">'.$code.'</script>';
}

function escape_javascript($javascript)
{
    $javascript = preg_replace('/\r\n|\n|\r/', '\\n', $javascript);
    $javascript = str_replace(array('"', "'"), array('\\\"', "\\\'"), $javascript);
    return $javascript;
}

function link_to_function($content, $function, $html_options = array())
{
    $options = array_merge(array('href' => '#', 'onclick' => $function.'; return false;'), $html_options);
    return content_tag('a', $content, $options);
}

/**
 * Returns a link to a remote action whose url is defined by $options['url'].
 * This action is called using xmlHttpRequest and the response can be inserted into the page, 
 * in a DOM object whose id is specified by $options['update'].
 * 
 * 
 * @return string
 **/
function link_to_remote($content, $options = array(), $html_options = array())
{
    return link_to_function($content, remote_function($options), $html_options);
}

function update_element_function($element_id, $options = array())
{
    if (!isset($options['action'])) $options['action'] = 'update';
    if (!isset($options['escape'])) $options['escape'] = true;
    if (!isset($options['content'])) $options['content'] = '';
    
    if ($options['escape'] === true) $content = escape_javascript($options['content']);
    else $content = $options['content'];
    
    switch ($options['action'])
    {
        case 'update':
            if (isset($options['position']))
                $js = 'new Insertion.'.SInflection::camelize($options['position'])."('$element_id', '$content')";
            else
                $js = "$('$element_id').innerHTML = '$content'";
            break;
        case 'empty':
            $js = "$('$element_id').innerHTML = ''";
            break;
        case 'remove':
            "Element.remove('$element_id')";
            break;
        default:
            throw new SException("Invalid action, choose one of 'update', 'remove', 'empty'");
    }
    
    $js.= ";\n";
    return $js;
    
}

function form_remote_tag($options = array())
{
    $options['form'] = True;
    if (!isset($options['html'])) $options['html'] = array();
    $options['html']['onsubmit'] = remote_function($options)."; return false;";
    if (!isset($options['html']['action'])) $options['html']['action'] = url_for($options['url']);
    
    return tag('form', $options['html'], True);
}

function observe_field($id, $options = array())
{
    if (isset($options['frequency']) && $options['frequency'] > 0)
        return build_observer('Form.Element.Observer', $id, $options);
    else
        return build_observer('Form.Element.EventObserver', $id, $options);
}

function observe_form($id, $options = array())
{
    if (isset($options['frequency']) && $options['frequency'] > 0)
        return build_observer('Form.Observer', $id, $options);
    else
        return build_observer('Form.EventObserver', $id, $options);
}

function in_place_editor_field($object_name, $method, $object, $tag_options = array(), $editor_options = array())
{
    $tag_options = array_merge(array('id' => "${object_name}_${method}_".$object->id."_in_place_editor",
    'class' => 'in_place_editor'), $tag_options);
    
    if (!isset($editor_options['url'])) 
        $editor_options['url'] = array('action' => "set_${object_name}_${method}", 'id' => $object->id);
        
    $tag = $tag_options['tag'];
    unset($tag_options['tag']);
    
    return content_tag($tag, $object->$method, $tag_options)
    .in_place_editor($tag_options['id'], $editor_options);
}

function in_place_editor($field_id, $options = array())
{
    $js = "new Ajax.InPlaceEditor(";
    $js.= "'{$field_id}',";
    $js.= "'".url_for($options['url'])."'";
    
    $js_options = array();
    if (isset($options['cancel_text'])) $js_options['cancelText'] = "'".$options['cancel_text']."'";
    if (isset($options['save_text'])) $js_options['okText'] = "'".$options['save_text']."'";
    if (isset($options['loading_text'])) $js_options['loadingText'] = "'".$options['loading_text']."'";
    if (isset($options['rows'])) $js_options['rows'] = $options['rows'];
    if (isset($options['cols'])) $js_options['cols'] = $options['cols'];
    if (isset($options['size'])) $js_options['size'] = $options['size'];
    if (isset($options['external_control'])) $js_options['externalControl'] = "'".$options['external_control']."'";
    if (isset($options['load_text_url'])) $js_options['loadTextURL'] = "'".url_for($options['load_text_url'])."'";
    if (isset($options['options'])) $js_options['ajaxOptions'] = $options['options'];
    if (isset($options['script'])) $js_options['evalScripts'] = $options['script'];
    if (isset($options['with'])) $js_options['callback'] = "function(form) { return ".$options['with']." }";
    
    if (!empty($js_options)) $js.= ', '.options_for_js($js_options);
    $js.= ')';
    
    return javascript_tag($js);
}

function text_field_with_auto_complete($object_name, $method, $object, $tag_options = array(), $completion_options = array())
{
    $tag_options['autocomplete'] = "off";
    
    $html = auto_complete_css()
    .text_field($object_name, $method, $object, $tag_options)
    .content_tag('div', '', array('id' => "${object_name}_${method}_auto_complete", 'class' => "auto_complete"))
    .auto_complete_field("${object_name}_${method}", array('url' => array('action' => "auto_complete_for_${object_name}_${method}")));
    
    return $html;
}

function auto_complete_field($id, $options = array())
{
    if (!isset($options['update'])) $options['update'] = $id.'_auto_complete';
    $js = "new Ajax.Autocompleter('$id', '".$options['update']."', '".url_for($options['url'])."'";
    
    $js_options = array();
    if (isset($options['with'])) $js_options['callback'] = "function(element, value) { return ".$options['with']." }";
    if (isset($options['indicator'])) $js_options['indicator'] = "'".$options['indicator']."'";
    $js.= ', '.options_for_js($js_options).')';
    
    return javascript_tag($js);
}

function remote_function($options)
{
    $js_options = options_for_ajax($options);
    if (isset($options['update']) && is_array($options['update']))
    {
        $updates = array();
        if (isset($options['update']['success']))
            $updates[] = "success:'".$options['update']['success']."'";
        if (isset($options['update']['failure']))
            $updates[] = "failure:'".$options['update']['failure']."'";
        $update = '{'.implode(',', $updates).'}';
    }
    elseif (isset($options['update']))
    {
        $update = "'".$options['update']."'";
    }
    
    $js = (!isset($update)) ? "new Ajax.Request(" : "new Ajax.Updater($update, ";
    $js.= "'".url_for($options['url'])."', $js_options)";
    
    if (isset($options['before']))      $js = $options['before']."; $js";
    if (isset($options['after']))       $js = "$js; ".$options['after'];
    if (isset($options['condition']))   $js = "if (".$options['condition'].") { $js; }";
    if (isset($options['confirm']))     $js = "if (confirm(".addslashes($options['confirm']).")) { $js; }";
    
    return $js;
}

function options_for_js($options)
{
    $set = array();
    foreach($options as $key => $code) $set[] = "$key:$code";
    return '{'.implode(',', $set).'}';
}

function options_for_ajax($options)
{
    $js_options = build_callbacks($options);
    $js_options['asynchronous'] = 'true';
    $js_options['method'] = "'post'";
    //$js_options['evalScripts'] = ?
    if (isset($options['position']) && in_array($options['position'], array('before', 'after', 'top', 'bottom')))
    {
        $js_options['insertion'] = 'Insertion.'.ucfirst($options['position']);
    }
    if ($options['form']) $js_options['parameters'] = 'Form.serialize(this)';
    elseif ($options['with']) $js_options['parameters'] = $options['with'];
    
    return options_for_js($js_options);
}

function build_callbacks($options)
{
    $callbacks = array();
    $events = array('Uninitialized', 'Loading', 'Loaded', 'Interactive', 'Complete', 'Success', 'Failure');
    foreach($options as $event => $code)
    {
        $event = ucfirst($event);
        if (in_array($event, $events)) $callbacks['on'.$event] = "function(request){{$code}}";
    }
    return $callbacks;
}

function build_observer($class, $id, $options = array())
{
    if (isset($options['with']) && strpos($options['with'], '=') === false)
        $options['with'] = "'".$options['with']."='+value";
    elseif (isset($options['update']))
        $options['with'] = 'value';
        
    $callback = remote_function($options);
    
    $js = "new $class('$id', ";
    if (isset($options['frequency']) && $options['frequency'] > 0) $js.= $options['frequency'].", ";
    $js.= "function(element, value) {";
    $js.= "$callback}";
    if (isset($options['on'])) $js.= ", '".$options['on']."'";
    $js.= ")";
    
    return javascript_tag($js);
}

function auto_complete_css()
{
    $css = <<<EOT
          div.auto_complete {
            width: 350px;
            background: #fff;
          }
          div.auto_complete ul {
            border:1px solid #888;
            margin:0;
            padding:0;
            width:100%;
            list-style-type:none;
          }
          div.auto_complete ul li {
            margin:0;
            padding:3px;
          }
          div.auto_complete ul li.selected { 
            background-color: #ffb; 
          }
          div.auto_complete ul strong.highlight { 
            color: #800; 
            margin:0;
            padding:0;
          }
EOT;
    return content_tag("style", $css);
}

?>
