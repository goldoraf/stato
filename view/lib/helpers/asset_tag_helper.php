<?php

if (!defined(JAVASCRIPT_DEFAULT_SOURCES))
    define('JAVASCRIPT_DEFAULT_SOURCES', 'controls;dragdrop;effects;prototype');

function image_tag($filename, $options = array())
{
    $options['src'] = image_path($filename);
    if (!isset($options['alt']))
    {
        list($alt, ) = explode('.', basename($options['src']));
        $options['alt'] = ucfirst($alt);
    }
    if (isset($options['size']))
    {
        list($options['width'], $options['height']) = explode('x', $options['size']);
        unset($options['size']);
    }
    return tag('img', $options);
}

function javascript_include_tag($sources)
{
    if (!is_array($sources)) $sources = array($sources);
    $html = '';
    foreach($sources as $source)
        $html.= '<script src="'.javascript_path($source).'" type="text/javascript"></script>'."\n";
    
    return $html;
}

function javascript_include_defaults()
{
    return javascript_include_tag(explode(';', JAVASCRIPT_DEFAULT_SOURCES));
}

function stylesheet_link_tag($sources, $options = array())
{
    if (!is_array($sources)) $sources = array($sources);
    $html = '';
    foreach($sources as $source)
    {
        $options = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen'), $options);
        $options['href'] = stylesheet_path($source);
        $html.= tag('link', $options)."\n";
    }
    return $html;
}

function image_path($source)
{
    return compute_public_path($source, 'images', 'png');
}

function javascript_path($source)
{
    return compute_public_path($source, 'js', 'js');
}

function stylesheet_path($source)
{
    return compute_public_path($source, 'styles', 'css');
}

function compute_public_path($source, $dir = 'images', $ext = Null)
{
    static $relativeUrlRoot = null;
    
    if (preg_match('/http|https/', $source)) return $source;
    
    if (strpos($source, '.') === false) $source.= ".{$ext}";
    
    if ($relativeUrlRoot == null)
        $relativeUrlRoot = str_replace('/index.php', '/', $_SERVER['SCRIPT_NAME']);
    return "{$relativeUrlRoot}{$dir}/{$source}";
}

?>
