<?php

define('XML_HTMLSAX3', STATO_CORE_PATH.'/vendor/safehtml/classes/');
require_once(XML_HTMLSAX3.'safehtml.php');

/**
 * Text helpers
 * 
 * Provides a set of functions for filtering, formatting and transforming strings.
 * 
 * @package Stato
 * @subpackage view
 */
/**
 * Convert special characters to HTML entities
 */
function html_escape($html)
{
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}

/**
 * If <var>$text</var> is longer than <var>$length</var>, <var>$text</var> will 
 * be truncated to the length of <var>$length</var> and the last three characters
 * will be replaced with the <var>$truncate_string</var>.
 */
function truncate($text, $length = 30, $truncate_string = '...')
{
    if (utf8_strlen($text) > $length)
        return utf8_substr_replace($text, $truncate_string, $length - utf8_strlen($truncate_string));
    else
        return $text;
}

/**
 * Attempts to pluralize the <var>$singular</var> word unless count is 1. 
 * It will use <var>$plural</var> if supplied, the <var>SInflection</var> class if defined,
 * otherwise it will just add an 's' to the string. 
 * 
 * <code>
 * pluralize(1, 'person');  => 1 person
 * pluralize(2, 'person');  => 2 people
 * pluralize(3, 'person', 'users');  => 3 users
 * </code>
 */
function pluralize($count, $singular, $plural = null)
{
    if ($count == 1) return $count.' '.$singular;
    if ($plural !== null) return $count.' '.$plural;
    if (class_exists('SInflection')) return $count.' '.SInflection::pluralize($singular);
    return $count.' '.$singular.'s';
}

/**
 * Sanitize the given HTML using safeHTML library. It is better than PHP function 
 * strip_tags(), who does not modify any attributes on the tags that you allow.
 *
 * The parser strips down all potentially dangerous content within HTML:
 *
 *  * opening tag without its closing tag 
 *  * closing tag without its opening tag 
 *  * any of these tags: “base”, “basefont”, “head”, “html”, “body”, “applet”, “object”,
 *    “iframe”, “frame”, “frameset”, “script”, “layer”, “ilayer”, “embed”, “bgsound”,
 *    “link”, “meta”, “style”, “title”, “blink”, “xml” etc.
 *  * any of these attributes: on*, data*, dynsrc
 *  * javascript:/vbscript:/about: etc. protocols
 *  * expression/behavior etc. in styles
 *  * any other active content
 */
function sanitize($html)
{
    $safehtml = new safehtml();
    return $safehtml->parse($html);
}

/**
 * Creates a SCycle object whose __toString() method cycles through elements of an array every time it is called.
 * 
 * This can be used for example, to alternate classes for table rows:
 * <code>
 * <? foreach ($this->items as $item) : ? >
 *  <tr class="<?= cycle(array("even", "odd")); ? >">
 *    <td>item</td>
 *  </tr>
 * <? endforeach; ? >
 * </code>
 * 
 * You can use named cycles to allow nesting in loops. You can manually reset a 
 * cycle by calling reset_cycle() and passing the name of the cycle.
 * <code>
 * <? foreach ($this->items as $item) : ? >
 *  <tr class="<?= cycle(array("even", "odd"), "row_class"); ? >">
 *    <td>
 *    <? foreach ($item->values as $value) : ? >
 *      <span style="<?= cycle(array("red", "green"), "colors"); ? >">value</span>
 *    <? endforeach; ? >
 *    <? reset_cycle("colors"); ? > 
 *    </td>
 *  </tr>
 * <? endforeach; ? >
 * </code>   
 */
function cycle($values, $name = 'default')
{
    $cycle = SCycle::get_cycle($name);
    if ($cycle === null || $cycle->values != $values)
        $cycle = SCycle::set_cycle($name, new SCycle($values));
    return $cycle->__toString();
}

function reset_cycle($name = 'default')
{
    $cycle = SCycle::get_cycle($name);
    if ($cycle !== null) $cycle->reset();
}

/**
 * @ignore
 */
class SCycle
{
    public $values = array();
    private $index = 0;
    
    private static $cycles = array();
    
    public function __construct($values)
    {
        $this->values = $values;
    }
    
    public function __toString()
    {
        $value = $this->values[$this->index];
        if ($this->index == count($this->values) - 1) $this->index = 0;
        else $this->index++;
        return $value;
    }
    
    public function reset()
    {
        $this->index = 0;
    }
    
    public static function set_cycle($name, $cycle)
    {
        self::$cycles[$name] = $cycle;
        return $cycle;
    }
    
    public static function get_cycle($name)
    {
        if (isset(self::$cycles[$name])) return self::$cycles[$name];
        else return null;
    }
}

?>
