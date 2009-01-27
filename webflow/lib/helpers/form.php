<?php

/**
 * Form helpers
 * 
 * @package Stato
 * @subpackage webflow
 */

/**
 * Create a select tag and a series of contained option tags
 * 
 * Example :
 * <code>
 * select('post[category]', array('Linux','BSD'), 'Linux', array('include_blank' => true));
 * </code>
 * could generate :
 * <code>
 * <select name="post[category]">
 *   <option></option>
 *   <option selected="selected">Linux</option>
 *   <option>BSD</option>
 * </select>
 * </code>
 */
function select($name, $choices, $selected = null, $options = array(), $html_options = array())
{
    $options_block = add_select_options(options_for_select($choices, $selected), $options, $selected);
    $tag_options = tag_options($html_options);
    return "<select name=\"{$name}\" {$tag_options}>{$options_block}</select>";
}

/**
 * Accepts an array and returns a string of option tags.
 * 
 * Examples :  
 * <code>
 * options_for_select(array('Margharita', 'Calzone', 'Napolitaine'));
 * // returns :
 * <option value="Margharita">Margharita</option>
 * <option value="Calzone">Calzone</option>
 * <option value="Napolitaine">Napolitaine</option>
 *  
 * options_for_select(array('Margharita'=>'7e', 'Calzone'=>'9e', 'Napolitaine'=>'8e'));
 * // returns :
 * <option value="7e">Margharita</option>
 * <option value="9e">Calzone</option>
 * <option value="8e">Napolitaine</option>
 * </code> 
 */
function options_for_select($set, $selected = null, $detect_non_associative = true)
{
    $str = '';
    if (!is_array($selected)) $selected = array($selected);
    foreach ($set as $lib => $value)
    {
        if ($detect_non_associative && is_int($lib)) $lib = $value; // non-associative array
        
        $str.= '<option value="'.html_escape($value).'"';
        if (in_array($value, $selected)) $str.= ' selected="selected"';
        $str.= '>'.html_escape($lib)."</option>\n";
    }
    return $str;
}

/**
 * @ignore
 */
function add_select_options($options_block, $options, $selected = null)
{
    if (isset($options['include_blank']) && $options['include_blank'] === true)
        $options_block = '<option value=""></option>'.$options_block;
    if (empty($selected) && isset($options['prompt']))
        $options_block = '<option value="">'.$options['prompt'].'</option>'.$options_block;
    return $options_block;
}

/**
 * @ignore
 */
function tag_options($options = array())
{
    if (count($options) == 0) return;
    $set = array();
    foreach($options as $key => $value)
    {
        if ($value !== null && $value !== false)
        {
            if ($value === true) $set[] = $key.'="'.$key.'"';
            else $set[] = $key.'="'.$value.'"';
        }
    }
    return implode(" ", $set);
}
