<?php

function content_tag($name, $content, $options = array())
{
    return "<$name".tag_options($options).">$content</$name>";
}

function tag($name, $options = array(), $open = False)
{
    return "<$name".tag_options($options).($open ? ">" : " />");
}

function img_tag($filename, $options = array())
{
    return '<img src="'.image_path($filename).'"'.tag_options($options).' />';
}

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
    return ' '.implode(" ", $set);
}

function js_tag($code)
{
    return '<script type="text/javascript">'.$code.'</script>';
}

function style_tag($code)
{
    return '<style type="text/css">'.$code.'</style>';
}

function js_include_tag($sources)
{
    if (!is_array($sources)) $sources = array($sources);
    $html = '';
    foreach($sources as $source)
        $html.= '<script src="'.compute_public_path($source, 'js').'" type="text/javascript"></script>';
    
    return $html;
}

function css_include_tag($source)
{
    if (!file_exists($source))
        $source = ROOT_DIR.'/public/styles/'.$source;
        
    return style_tag(file_get_contents($source));
}

function css_link_tag($sources, $options = array())
{
    if (!is_array($sources)) $sources = array($sources);
    $html = '';
    foreach($sources as $source)
    {
        $options = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen'), $options);
        $options['href'] = compute_public_path($source, 'styles');
        $html.= tag('link', $options)."\n";
    }
    return $html;
}

function image_path($source)
{
    return compute_public_path($source, 'images');
}

function compute_public_path($source, $dir, $ext = Null)
{
    static $relativeUrlRoot = null;
    if ($relativeUrlRoot == null)
        $relativeUrlRoot = str_replace('/index.php', '/', $_SERVER['SCRIPT_NAME']);
    return "{$relativeUrlRoot}{$dir}/{$source}";
}

?>
