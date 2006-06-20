<?php

function javascript_tag($code)
{
    return '<script type="text/javascript">'.$code.'</script>';
}

function link_to_function($content, $function, $htmlOptions = array())
{
    $options = array_merge(array('href' => '#', 'onclick' => $function.'; return false;'), $htmlOptions);
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
function link_to_remote($content, $options = array(), $htmlOptions = array())
{
    return link_to_function($content, remote_function($options), $htmlOptions);
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

function in_place_editor_field($objectName, $method, $object, $tagOptions = array(), $editorOptions = array())
{
    $tagOptions = array_merge(array('id' => "${objectName}_${method}_".$object->id."_in_place_editor",
    'class' => 'in_place_editor'), $tagOptions);
    
    if (!isset($editorOptions['url'])) 
        $editorOptions['url'] = array('action' => "set_${objectName}_${method}", 'id' => $object->id);
        
    $tag = $tagOptions['tag'];
    unset($tagOptions['tag']);
    
    return content_tag($tag, $object->$method, $tagOptions)
    .in_place_editor($tagOptions['id'], $editorOptions);
}

function in_place_editor($fieldId, $options = array())
{
    $js = "new Ajax.InPlaceEditor(";
    $js.= "'{$fieldId}',";
    $js.= "'".url_for($options['url'])."'";
    
    $jsOptions = array();
    if (isset($options['cancel_text'])) $jsOptions['cancelText'] = "'".$options['cancel_text']."'";
    if (isset($options['save_text'])) $jsOptions['okText'] = "'".$options['save_text']."'";
    if (isset($options['loading_text'])) $jsOptions['loadingText'] = "'".$options['loading_text']."'";
    if (isset($options['rows'])) $jsOptions['rows'] = $options['rows'];
    if (isset($options['cols'])) $jsOptions['cols'] = $options['cols'];
    if (isset($options['size'])) $jsOptions['size'] = $options['size'];
    if (isset($options['external_control'])) $jsOptions['externalControl'] = "'".$options['external_control']."'";
    if (isset($options['load_text_url'])) $jsOptions['loadTextURL'] = "'".url_for($options['load_text_url'])."'";
    if (isset($options['options'])) $jsOptions['ajaxOptions'] = $options['options'];
    if (isset($options['script'])) $jsOptions['evalScripts'] = $options['script'];
    if (isset($options['with'])) $jsOptions['callback'] = "function(form) { return ".$options['with']." }";
    
    if (!empty($jsOptions)) $js.= ', '.options_for_js($jsOptions);
    $js.= ')';
    
    return javascript_tag($js);
}

function text_field_with_auto_complete($objectName, $method, $object, $tagOptions = array(), $completionOptions = array())
{
    $tagOptions['autocomplete'] = "off";
    
    $html = auto_complete_css()
    .text_field($objectName, $method, $object, $tagOptions)
    .content_tag('div', '', array('id' => "${objectName}_${method}_auto_complete", 'class' => "auto_complete"))
    .auto_complete_field("${objectName}_${method}", array('url' => array('action' => "auto_complete_for_${objectName}_${method}")));
    
    return $html;
}

function auto_complete_field($id, $options = array())
{
    if (!isset($options['update'])) $options['update'] = $id.'_auto_complete';
    $js = "new Ajax.Autocompleter('$id', '".$options['update']."', '".url_for($options['url'])."'";
    
    $jsOptions = array();
    if (isset($options['with'])) $jsOptions['callback'] = "function(element, value) { return ".$options['with']." }";
    if (isset($options['indicator'])) $jsOptions['indicator'] = "'".$options['indicator']."'";
    $js.= ', '.options_for_js($jsOptions).')';
    
    return javascript_tag($js);
}

function remote_function($options)
{
    $jsOptions = options_for_ajax($options);
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
    $js.= "'".url_for($options['url'])."', $jsOptions)";
    
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
    $jsOptions = build_callbacks($options);
    $jsOptions['asynchronous'] = 'true';
    $jsOptions['method'] = "'post'";
    //$jsOptions['evalScripts'] = ?
    if (isset($options['position']) && in_array($options['position'], array('before', 'after', 'top', 'bottom')))
    {
        $jsOptions['insertion'] = 'Insertion.'.ucfirst($options['position']);
    }
    if ($options['form']) $jsOptions['parameters'] = 'Form.serialize(this)';
    elseif ($options['with']) $jsOptions['parameters'] = $options['with'];
    
    return options_for_js($jsOptions);
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
