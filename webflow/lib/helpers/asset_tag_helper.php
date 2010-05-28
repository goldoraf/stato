<?php

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
    static $relative_url_root = null;
    
    if (preg_match('/http|https/', $source)) return $source;
    
    if (strpos($source, '.') === false) $source.= ".{$ext}";
    
    if ($relative_url_root == null)
    {
        if (SActionController::$asset_host === null)
            $relative_url_root = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        else
            $relative_url_root = SActionController::$asset_host;
    }
        
    return "{$relative_url_root}/{$dir}/{$source}";
}

?>