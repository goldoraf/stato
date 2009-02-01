<?php

/**
 * Number helpers
 * 
 * @package Stato
 * @subpackage webflow
 */

/**
 * Formats a <var>$number</var> with grouped thousands using <var>$delimiter</var>.
 * 
 * Examples:
 * <code>
 * number_with_delimiter(12345678)      => 12,345,678
 * number_with_delimiter(12345678, ".") => 12.345.678
 * </code>    
 */
function number_with_delimiter($number, $delimiter=',')
{
    return preg_replace('/(\d)(?=(\d\d\d)+(?!\d))/', "\\1{$delimiter}", $number);
}

/**
 * Formats a <var>$number</var> with the specified level of <var>$precision</var>.
 * 
 * Examples:
 * <code>
 * number_with_precision(123.45678)    => 123.456
 * number_with_precision(123.45678, 2) => 123.45
 * </code>    
 */
function number_with_precision($number, $precision=3)
{
    return sprintf("%01.{$precision}f", $number);
}
