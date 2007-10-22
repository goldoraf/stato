<?php

/**
 * I18n helpers
 * 
 * @package Stato
 * @subpackage view
 */
/**
 * Returns translation corresponding to <var>$key</var>.
 */
function __($key, $args = array(null))
{
    return SLocale::translate($key, $args);
}

?>
