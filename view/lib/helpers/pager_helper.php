<?php

/**
 * Creates a basic link bar for a given paginator.
 * 
 * Options :
 * - param_name : the routing name for the paginator (default : page)
 * - window_size : the number of pages to show around the current page (default : 2)
 * - show_anchors : whether or not the first and last pages should be shown (default : true)
 * - link_to_current_page : whether or not the current page should be linked to (default : false)
 * - params : any additional routing parameters for page URLs
 */
function pager($paginator, $options = array(), $html_options = array())
{
    $param_name   = (!isset($options['param_name']) ? 'page' : $options['param_name']);
    $window_size  = (!isset($options['window_size']) ? 2 : $options['window_size']);
    $show_anchors = (!isset($options['show_anchors']) ? True : $options['show_anchors']);
    $params       = (!isset($options['params']) ? array() : $options['params']);
    
    $link_to_current_page = (!isset($options['link_to_current_page']) ? array() : $options['link_to_current_page']);
    
    $current_page = $paginator->currentPage;
    $page_count   = $paginator->pageCount();
    $window_pages = $paginator->windowPages($window_size);
    $wp_count     = count($window_pages);
    
    $first = 1;
    $last  = $page_count;
    
    $html = '';
    
    if ($show_anchors && ($wp_first = $window_pages[0]) != $first)
    {
        $html.= pager_link($first, $param_name, $params, $html_options);
        if (($wp_first - $first) > 1) $html.= '...';
        $html.= ' ';
    }
    
    foreach ($window_pages as $page)
    {
        if ($current_page == $page && !$link_to_current_page) $html.= $page;
        else $html.= pager_link($page, $param_name, $params, $html_options);
        $html.= ' ';
    }
    
    if ($show_anchors && ($wp_last = $window_pages[$wp_count-1]) != $last)
    {
        if (($last - $wp_last) > 1) $html.= '...';
        $html.= pager_link($last, $param_name, $params, $html_options);
    }

    return $html;
}

function pager_link($page, $param_name, $params, $html_options)
{
    $params[$param_name] = $page;
    return link_to($page, $params, $html_options);
}

?>
