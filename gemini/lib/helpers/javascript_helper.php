<?php

/**
 * Javascript helpers
 * 
 * Set of functions for working with javascript
 * 
 * @package Stato
 * @subpackage view
 */
/**
 * Returns a javascript tag with the <var>$code</var> inside
 */
function javascript_tag($code)
{
    return '<script type="text/javascript">'.$code.'</script>';
}
/**
 * Escape carrier returns and single and double quotes for javascript code
 */
function escape_javascript($javascript)
{
    $javascript = preg_replace('/\r\n|\n|\r/', '\\n', $javascript);
    $javascript = str_replace(array('"', "'"), array('\\\"', "\\\'"), $javascript);
    return $javascript;
}
/**
 * Returns a link that will trigger a javascript function using the onclick handler
 * 
 * Example :
 * <code>link_to_function("Hello", "alert('Hello world !')");
 * Produces:
 *    <a onclick="alert('Hello world !'); return false;" href="#">Hello</a></code>
 **/
function link_to_function($content, $function, $html_options = array())
{
    $options = array_merge(array('href' => '#', 'onclick' => $function.'; return false;'), $html_options);
    return content_tag('a', $content, $options);
}

?>
