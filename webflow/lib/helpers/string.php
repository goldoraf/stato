<?php

/**
* Makes an underscored, lowercase form from the <var>$camel_cased_word</var> argument.
* 
* Example : <code>underscore('ActionController');  // => 'action_controller'</code>          
*/
function underscore($camel_cased_word)
{
    return strtolower(preg_replace('/([a-z\d])([A-Z])/', '\1_\2', 
                      preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $camel_cased_word)));
}

/**
 * Replaces underscores with dashes in the <var>$underscore_word</var> string.
 * 
 * Example : <code>dasherize('action_controller');  // => 'action-controller'</code>          
 */
function dasherize($underscore_word)
{
    return preg_replace('/_/', '-', $underscore_word);
}

/**
 * Converts the <var>$underscore_word</var> argument to UpperCamelCase style.
 * 
 * Example : <code>camelize('action_controller');  // => 'ActionController'</code>          
 */
function camelize($underscore_word)
{
    return preg_replace('/(^|_)(.)/e', "strtoupper('\\2')", $underscore_word);
}

/**
 * Capitalizes the first word, turns underscores into spaces and strips _id.
 * 
 * Example : <code>
 * humanize('date_of_birth');  // => 'Date of birth'
 * humanize('user_id');  // => 'User'
 * </code>          
 */
function humanize($word)
{
    return ucfirst(preg_replace('/_/', ' ', preg_replace('/_id/', '', $word)));
}

/**
 * Transforms a sentence into an underscored wiki page name.
 * 
 * Example : <code>wikify('Hello world');  // => 'hello_world'</code>          
 */
function wikify($sentence)
{
    return strtolower(preg_replace('/\W/', '', preg_replace('/\s/', '_', $sentence)));
}

/**
 * Transforms a sentence into a URL-usable string.
 * 
 * Example : <code>urlize('Hello world');  // => 'hello-world'</code>          
 */
function urlize($sentence)
{
    $sentence = deaccent($sentence);
    $sentence = preg_replace('/[^a-z0-9_-\s]/','',strtolower($sentence));
    $sentence = preg_replace('/[\s]+/',' ',trim($sentence));
    $sentence = str_replace(' ','-',$sentence);
    
    return $sentence;
}

/**
 * Sanitizes an upload's filename by removing non-alphanumeric characters, deaccenting and 
 * replacing spaces by underscores.
 * 
 * Example : <code>sanitize_filename('Compte rendu réunion.doc');  // => 'compte_rendu_reunion.doc'</code>          
 */
function sanitize_filename($filename)
{
    $filename = deaccent($filename);
    $filename = preg_replace('/[^a-z0-9_\.-\s]/','',strtolower($filename));
    $filename = preg_replace('/[\s]+/',' ',trim($filename));
    $filename = str_replace(' ','_',$filename);
    
    return $filename;
}

/**
 * Replace accented UTF-8 characters by unaccented ASCII-7 equivalents
 *
 * Use the optional parameter to just deaccent lower ($case = -1) or upper ($case = 1)
 * letters. Default is to deaccent both cases ($case = 0)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function deaccent($string, $case=0)
{
    static $utf8_lower_accents = array
    (
        'à' => 'a', 'ô' => 'o', 'd' => 'd', '?' => 'f', 'ë' => 'e', 'š' => 's', 'o' => 'o', 
        'ß' => 'ss', 'a' => 'a', 'r' => 'r', '?' => 't', 'n' => 'n', 'a' => 'a', 'k' => 'k', 
        's' => 's', '?' => 'y', 'n' => 'n', 'l' => 'l', 'h' => 'h', '?' => 'p', 'ó' => 'o', 
        'ú' => 'u', 'e' => 'e', 'é' => 'e', 'ç' => 'c', '?' => 'w', 'c' => 'c', 'õ' => 'o', 
        '?' => 's', 'ø' => 'o', 'g' => 'g', 't' => 't', '?' => 's', 'e' => 'e', 'c' => 'c', 
        's' => 's', 'î' => 'i', 'u' => 'u', 'c' => 'c', 'e' => 'e', 'w' => 'w', '?' => 't', 
        'u' => 'u', 'c' => 'c', 'ö' => 'oe', 'è' => 'e', 'y' => 'y', 'a' => 'a', 'l' => 'l', 
        'u' => 'u', 'u' => 'u', 's' => 's', 'g' => 'g', 'l' => 'l', 'ƒ' => 'f', 'ž' => 'z', 
        '?' => 'w', '?' => 'b', 'å' => 'a', 'ì' => 'i', 'ï' => 'i', '?' => 'd', 't' => 't', 
        'r' => 'r', 'ä' => 'ae', 'í' => 'i', 'r' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o', 
        'e' => 'e', 'ñ' => 'n', 'n' => 'n', 'h' => 'h', 'g' => 'g', 'd' => 'd', 'j' => 'j', 
        'ÿ' => 'y', 'u' => 'u', 'u' => 'u', 'u' => 'u', 't' => 't', 'ý' => 'y', 'o' => 'o', 
        'â' => 'a', 'l' => 'l', '?' => 'w', 'z' => 'z', 'i' => 'i', 'ã' => 'a', 'g' => 'g', 
        '?' => 'm', 'o' => 'o', 'i' => 'i', 'ù' => 'u', 'i' => 'i', 'z' => 'z', 'á' => 'a', 
        'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',
    );
    
    static $utf8_upper_accents = array
    (
        'À' => 'A', 'Ô' => 'O', 'D' => 'D', '?' => 'F', 'Ë' => 'E', 'Š' => 'S', 'O' => 'O', 
        'A' => 'A', 'R' => 'R', '?' => 'T', 'N' => 'N', 'A' => 'A', 'K' => 'K', 
        'S' => 'S', '?' => 'Y', 'N' => 'N', 'L' => 'L', 'H' => 'H', '?' => 'P', 'Ó' => 'O', 
        'Ú' => 'U', 'E' => 'E', 'É' => 'E', 'Ç' => 'C', '?' => 'W', 'C' => 'C', 'Õ' => 'O', 
        '?' => 'S', 'Ø' => 'O', 'G' => 'G', 'T' => 'T', '?' => 'S', 'E' => 'E', 'C' => 'C', 
        'S' => 'S', 'Î' => 'I', 'U' => 'U', 'C' => 'C', 'E' => 'E', 'W' => 'W', '?' => 'T', 
        'U' => 'U', 'C' => 'C', 'Ö' => 'Oe', 'È' => 'E', 'Y' => 'Y', 'A' => 'A', 'L' => 'L', 
        'U' => 'U', 'U' => 'U', 'S' => 'S', 'G' => 'G', 'L' => 'L', 'ƒ' => 'F', 'Ž' => 'Z', 
        '?' => 'W', '?' => 'B', 'Å' => 'A', 'Ì' => 'I', 'Ï' => 'I', '?' => 'D', 'T' => 'T', 
        'R' => 'R', 'Ä' => 'Ae', 'Í' => 'I', 'R' => 'R', 'Ê' => 'E', 'Ü' => 'Ue', 'Ò' => 'O', 
        'E' => 'E', 'Ñ' => 'N', 'N' => 'N', 'H' => 'H', 'G' => 'G', 'Ð' => 'D', 'J' => 'J', 
        'Ÿ' => 'Y', 'U' => 'U', 'U' => 'U', 'U' => 'U', 'T' => 'T', 'Ý' => 'Y', 'O' => 'O', 
        'Â' => 'A', 'L' => 'L', '?' => 'W', 'Z' => 'Z', 'I' => 'I', 'Ã' => 'A', 'G' => 'G', 
        '?' => 'M', 'O' => 'O', 'I' => 'I', 'Ù' => 'U', 'I' => 'I', 'Z' => 'Z', 'Á' => 'A', 
        'Û' => 'U', 'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae',
    );
    
    if ($case <= 0)
        $string = str_replace(array_keys($utf8_lower_accents), array_values($utf8_lower_accents), $string);
    
    if ($case >= 0)
        $string = str_replace(array_keys($utf8_upper_accents), array_values($utf8_upper_accents), $string);
    
    return $string;
}