<?php

define('XML_HTMLSAX3', ROOT_DIR.'/lib/safehtml-1.3.7/classes/');
require_once(XML_HTMLSAX3.'safehtml.php');

function html_escape($html)
{
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}

function truncate($text, $length = 30, $truncateString = '...')
{
    if (utf8_strlen($text) > $length)
        return utf8_substr_replace($text, $truncateString, $length - utf8_strlen($truncateString));
    else
        return $text;
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

function cycle($values, $name = 'default')
{
    $cycle = SCycle::getCycle($name);
    if ($cycle === null || $cycle->values != $values)
        $cycle = SCycle::setCycle($name, new SCycle($values));
    return $cycle->__toString();
}

function reset_cycle($name = 'default')
{
    $cycle = SCycle::getCycle($name);
    if ($cycle !== null) $cycle->reset();
}

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
    
    public static function setCycle($name, $cycle)
    {
        self::$cycles[$name] = $cycle;
        return $cycle;
    }
    
    public static function getCycle($name)
    {
        if (isset(self::$cycles[$name])) return self::$cycles[$name];
        else return null;
    }
}

?>
