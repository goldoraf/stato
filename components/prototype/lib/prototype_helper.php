<?php

if (!defined('JAVASCRIPT_PROTOTYPE_SOURCES'))
    define('JAVASCRIPT_PROTOTYPE_SOURCES', 'prototype;effects;controls;dragdrop;lowpro;builder;slider');
    
function javascript_include_prototype()
{
    return javascript_include_tag(explode(';', JAVASCRIPT_PROTOTYPE_SOURCES));
}

/**
 * Returns a link to a remote action whose url is defined by <var>$options['url']</var>.
 * This action is called using xmlHttpRequest and the response can be inserted into the page, 
 * in a DOM object whose id is specified by <var>$options['update']</var>. Usually, the response would 
 * be a partial prepared by the controller with either render_partial() or render_partial_collection().
 * 
 * Example :
 * <code>link_to_remote("Delete this post", array('update' => 'posts', 
 *     'url' => array('action' => 'destroy', 'id' => $this->post->id)));</code>
 *     
 * You can also specify an array for <var>$options['update']</var> to allow for 
 * easy redirection of output to an other DOM element if a server-side error occurs.
 * 
 * Example :
 * <code>link_to_remote("Delete this post", array('url' => array('action' => 'destroy', 'id' => $this->post->id),
 *     'update' => array('success' => 'posts', 'failure' => 'errors')));</code>
 *     
 * Optionally, you can use the <var>$options['position']</var> parameter to influence 
 * how the target DOM element is updated. It must be one of 'before', 'top', 'bottom', or 'after'.
 * 
 * By default, these remote requests are processed asynchronous during which various 
 * JavaScript callbacks can be triggered (for progress indicators and the likes). 
 * All callbacks get access to the request object, which holds the underlying XMLHttpRequest.
 * To access the server response, use request.responseText, to find out the HTTP status, use request.status.
 * 
 * The callbacks that may be specified are (in order):
 * <ul>
 * <li><var>loading</var>:     Called when the remote document is being loaded with data by the browser.</li>
 * <li><var>loaded</var>:      Called when the browser has finished loading the remote document.</li>
 * <li><var>interactive</var>: Called when the user can interact with the remote document, even though it has not finished loading.</li>
 * <li><var>success</var>:     Called when the XMLHttpRequest is completed, and the HTTP status code is in the 2XX range.</li>
 * <li><var>failure</var>:     Called when the XMLHttpRequest is completed, and the HTTP status code is not in the 2XX range.</li>
 * <li><var>complete</var>:    Called when the XMLHttpRequest is complete (fires after success/failure if they are present).</li>
 * </ul>
 * 
 * Example :
 * <code>link_to_remote("Delete this post", array('url' => array('action' => 'destroy', 'id' => $this->post->id),
 *     'update'  => 'posts', 'failure' => "alert('HTTP Error ' + request.status + '!')"));</code>
 * 
 * @return string
 **/
function link_to_remote($content, $options = array(), $html_options = array())
{
    return link_to_function($content, remote_function($options), $html_options);
}
/**
 * @ignore
 */
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
            throw new Exception("Invalid action, choose one of 'update', 'remove', 'empty'");
    }
    
    $js.= ";\n";
    return $js;
    
}
/**
 * Returns a form tag that will submit using XMLHttpRequest in the background
 **/
function form_remote_tag($options = array())
{
    $options['form'] = True;
    if (!isset($options['html'])) $options['html'] = array();
    $options['html']['onsubmit'] = remote_function($options)."; return false;";
    if (!isset($options['html']['action'])) $options['html']['action'] = url_for($options['url']);
    
    return tag('form', $options['html'], True);
}
/**
 * Observes the field with the DOM ID specified by <var>$id</var> and makes an Ajax call when its contents have changed.
 *
 * Required options are either of:
 * <ul> 
 * <li><var>url:</var>      url_for-style options for the action to call when the field has changed.</li>
 * <li><var>function:</var> Instead of making a remote call to a URL, you can specify a function to be called instead.</li>
 * </ul>
 * 
 * Additional options are:
 * <ul> 
 * <li><var>frequency:</var> The frequency (in seconds) at which changes to this field will be detected. 
 * Not setting this option at all or to a value equal to or less than zero will use event based observation instead of time based observation.</li>
 * <li><var>update:</var>    Specifies the DOM ID of the element whose innerHTML should be updated with the XMLHttpRequest response text.</li>
 * <li><var>with:</var>      A JavaScript expression specifying the parameters for the XMLHttpRequest. 
 * This defaults to 'value', which in the evaluated context refers to the new field value. 
 * If you specify a string without a "=", it'll be extended to mean the form key that the value should be assigned to. 
 * So <var>'with' => "term"</var> gives "'term'=value". If a "=" is present, no extension will happen.</li>
 * <li><var>on:</var>        Specifies which event handler to observe. By default, 
 * it's set to "changed" for text fields and areas and "click" for radio buttons and checkboxes. 
 * With this, you can specify it instead to be "blur" or "focus" or any other event.</li>
 * </ul>
 * 
 * Additionally, you may specify any of the options documented in link_to_remote.
 **/
function observe_field($id, $options = array())
{
    if (isset($options['frequency']) && $options['frequency'] > 0)
        return build_observer('Form.Element.Observer', $id, $options);
    else
        return build_observer('Form.Element.EventObserver', $id, $options);
}
/**
 * Like <var>observe_field</var>, but operates on an entire form identified by the DOM ID 
 * <var>$id</var>. 
 * 
 * Options are the same as <var>observe_field</var>, except the default value 
 * of the <var>with</var> option evaluates to the serialized (request string) value of the form.
 **/
function observe_form($id, $options = array())
{
    if (isset($options['frequency']) && $options['frequency'] > 0)
        return build_observer('Form.Observer', $id, $options);
    else
        return build_observer('Form.EventObserver', $id, $options);
}

function visual_effect($name, $id = false, $options = array())
{
    $element = ($id) ? "'$id'" : "element";
    
    if (isset($options['queue']))
    {
        if (is_array($options['queue']))
        {
            $temp = array();
            foreach ($options['queue'] as $k => $v) $temp[] = ($k == 'limit') ? "$k:$v" : "$k:'$v'";
            $options['queue'] = '{'.implode(',', $temp).'}';
        }
        else $options['queue'] = "'".$options['queue']."'";
    }
    
    if (in_array($name, array('toggle_appear', 'toggle_slide', 'toggle_blind')))
        return "Effect.toggle($element, '".str_replace('toggle_', '', $name)."', ".options_for_js($options).");";
    else
        return "new Effect.".SInflection::camelize($name)."($element, ".options_for_js($options).");";
}

function in_place_editor_field($object_name, $method, $object, $tag_options = array(), $editor_options = array())
{
    $tag_options = array_merge(array('id' => "${object_name}_${method}_".$object->id."_in_place_editor",
    'class' => 'in_place_editor', 'tag' => 'span'), $tag_options);
    
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

function sortable_element($id, $options = array())
{
    if (!isset($options['with'])) $options['with'] = "Sortable.serialize('$id')";
    if (!isset($options['onUpdate'])) $options['onUpdate'] = "function(){".remote_function($options)."}";
    
    foreach ($options as $k => $v) if (in_array($k, ajax_options())) unset($options[$k]);
    foreach (array('tag', 'overlap', 'constraint', 'handle') as $option)
        if (isset($options[$option])) $options[$option] = "'".$options[$option]."'";
    
    if (isset($options['containment'])) $options['containment'] = array_or_string_for_js($options['containment']);
    if (isset($options['only'])) $options['only'] = array_or_string_for_js($options['only']);
    
    return javascript_tag("Sortable.create('$id', ".options_for_js($options).");");
}

function draggable_element($id, $options = array())
{
    return javascript_tag("new Draggable('$id', ".options_for_js($options).");");
}

function drop_receiving_element($id, $options = array())
{
    if (!isset($options['with'])) $options['with'] = "'id=' + encodeURIComponent(element.id)";
    if (!isset($options['onDrop'])) $options['onDrop'] = "function(element){".remote_function($options)."}";
    
    foreach ($options as $k => $v) if (in_array($k, ajax_options())) unset($options[$k]);
    
    if (isset($options['accept'])) $options['accept'] = array_or_string_for_js($options['accept']);  
    if (isset($options['hoverclass'])) $options['hoverclass'] = "'".$options['hoverclass']."'";
    
    return javascript_tag("Droppables.add('$id', ".options_for_js($options).");");
}

/**
 * Returns the JavaScript needed for a remote function. Takes the same arguments as link_to_remote.
 * 
 * Example :
 * <code><select id="options" onchange="<?= remote_function(array('update' => "options", 'url' => array('action' => 'update_options'))); ? >">
 *   <option value="0">Hello</option>
 *   <option value="1">World</option>
 * </select></code>
 **/
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
/**
 * @ignore
 */
function options_for_js($options)
{
    $set = array();
    foreach($options as $key => $code) $set[] = "$key:$code";
    return '{'.implode(',', $set).'}';
}
/**
 * @ignore
 */
function options_for_ajax($options)
{
    $js_options = build_callbacks($options);
    $js_options['asynchronous'] = 'true';
    $js_options['method'] = "'post'";
    $js_options['evalScripts'] = 'true';
    if (isset($options['position']) && in_array($options['position'], array('before', 'after', 'top', 'bottom')))
    {
        $js_options['insertion'] = 'Insertion.'.ucfirst($options['position']);
    }
    if (@$options['form'] === true) $js_options['parameters'] = 'Form.serialize(this)';
    elseif (@$options['with']) $js_options['parameters'] = $options['with'];
    
    return options_for_js($js_options);
}
/**
 * @ignore
 */
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
/**
 * @ignore
 */
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
/**
 * @ignore
 */
function ajax_options()
{
    return array('before', 'after', 'condition', 'url',
    'asynchronous', 'method', 'insertion', 'position', 'form', 'with', 'update', 'script',
    'uninitialized', 'loading', 'loaded', 'interactive', 'complete', 'failure', 'success');
}
/**
 * @ignore
 */
function array_or_string_for_js($option)
{
    if (is_array($option))
        return "['".implode("','", $option)."']";
    else
        return "'$option'";
}
/**
 * @ignore
 */
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
