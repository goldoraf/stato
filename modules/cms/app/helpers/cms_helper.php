<?php

function cms_page_link($page)
{
    return link_to($page->title, page_url(array('path' => $page->full_path)));
}

function cms_link_to_unless_current_page($label, $url)
{
    $html_options = array();
    if (is_array($url)) $url = url_for($url);
    if ($url == '/'.SUrlRewriter::request_uri()) $html_options['class'] = 'current';
    return link_to($label, $url, $html_options);
}

function cms_link_to_unless_current_root($label, $path)
{
    $html_options = array();
    $current_path = SUrlRewriter::request_param('path');
    if (strpos('/'.$current_path, $path) !== false) $html_options['class'] = 'current';
    return link_to($label, $path, $html_options);
}

?>
