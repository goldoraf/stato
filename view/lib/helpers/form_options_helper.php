<?php

/**
 * Create a select tag and a series of contained option tags for the provided object and method. 
 * The option currently held by the object will be selected.
 * See options_for_select for the required format of the <var>$choices</var> argument. 
 * 
 * Example :
 * <code>
 * select('post', 'category', Post::$categories, array('include_blank' => True));
 * </code>
 * could generate :
 * <code>
 * <select id="post_category" name="post[category]">
 *   <option></option>
 *   <option>PHP</option>
 *   <option>Linux</option>
 *   <option>Space</option>
 * </select>
 * </code>
 */
function select($object_name, $method, $object, $choices, $options = array(), $html_options = array())
{
    list($name, $value, $html_options) = default_options($object_name, $method, $object, $html_options);
    $options_block = add_select_options(options_for_select($choices, $value), $options, $value);
    return select_tag($name, $options_block, $html_options);
}

/**
 * Return select and option tags for the given object and method using options_from_collection_for_select to generate the list of option tags.
 */
function collection_select($object_name, $method, $object, $collection, $value_prop='id', $text_prop=null, $options=array(), $html_options = array())
{
    list($name, $value, $html_options) = default_options($object_name, $method, $object, $html_options);
    $options_block = add_select_options(options_from_collection_for_select($collection, $value_prop, $text_prop, $value), $options, $value);
    return select_tag($name, $options_block, $html_options);
}

function add_select_options($options_block, $options, $value = null)
{
    if ($options['include_blank']) $options_block = '<option value=""></option>'.$options_block;
    if (empty($value) && isset($options['prompt']))
        $options_block = '<option value="">'.$options['prompt'].'</option>'.$options_block;
    return $options_block;
}

/**
 * Accepts a an array and returns a string of option tags.
 * 
 * Examples :  
 * <code>
 * options_for_select(array('Margharita', 'Calzone', 'Napolitaine'));
 * // returns :
 * <option value="Margharita">Margharita</option>
 * <option value="Calzone">Calzone</option>
 * <option value="Napolitaine">Napolitaine</option>
 *  
 * options_for_select(array('Margharita'=>'7€', 'Calzone'=>'9€', 'Napolitaine'=>'8€'));
 * // returns :
 * <option value="7€">Margharita</option>
 * <option value="9€">Calzone</option>
 * <option value="8€">Napolitaine</option> 
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

function options_from_collection_for_select($collection, $value_prop='id', $text_prop=null, $selected=null)
{
    $set = array();
    foreach ($collection as $entity)
    {
        if ($text_prop === null) $set[$entity->__repr()] = $entity->$value_prop;
        else $set[$entity->$text_prop] = $entity->$value_prop;
    }
    return options_for_select($set, $selected, false);
}

function options_groups_from_collection_for_select()
{

}

?>
