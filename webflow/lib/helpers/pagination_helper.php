<?php

/**
 * Pagination helpers
 * 
 * @package Stato
 * @subpackage view
 */
/**
 * Creates a basic link bar for a given <var>$paginator</var>.
 * 
 * Options :
 * - param_name : the routing name for the paginator (default : page)
 * - window_size : the number of pages to show around the current page (default : 2)
 * - separator : the string separator for page HTML elements (default : single space)
 * - show_anchors : whether or not the first and last pages should be shown (default : true)
 * - link_to_current_page : whether or not the current page should be linked to (default : false)
 * - params : any additional routing parameters for page URLs
 */
function pagination_links($paginator, $options = array(), $html_options = array())
{
    $p = new SPaginationHelper($paginator, $options, $html_options);
    return $p->links();
}

/**
 * @ignore
 */
class SPaginationHelper
{
    protected $paginator    = null;
    protected $param_name   = 'page';
    protected $window_size  = 2;
    protected $separator    = ' ';
    protected $show_anchors = true;
    protected $params       = array();
    protected $html_options = array();
    protected $link_to_current_page = false;
    
    public function __construct($paginator, $options = array(), $html_options = array())
    {
        $this->paginator = $paginator;
        $this->html_options = $html_options;
        
        if (isset($options['param_name']))   $this->param_name = $options['param_name'];
        if (isset($options['window_size']))  $this->window_size = $options['window_size'];
        if (isset($options['separator']))    $this->window_size = $options['separator'];
        if (isset($options['show_anchors'])) $this->show_anchors = $options['show_anchors'];
        if (isset($options['params']))       $this->params = $options['params'];
        
        if (isset($options['link_to_current_page']))
            $this->link_to_current_page = $options['link_to_current_page'];
    }
    
    public function links()
    {
        $current_page = $this->paginator->current_page;
        $page_count   = $this->paginator->page_count();
        $window_pages = $this->paginator->window_pages($this->window_size);
        $wp_count     = count($window_pages);
        
        $links = array();
        $first = 1;
        $last  = $page_count;
        
        if (count($window_pages) <= 1 && !$this->link_to_current_page) return;
        
        if ($this->show_anchors && ($wp_first = $window_pages[0]) != $first)
        {
            $links[] = $this->link($first);
            if (($wp_first - $first) > 1) $links[] = '...';
        }
        
        foreach ($window_pages as $page)
        {
            if ($current_page == $page && !$this->link_to_current_page) $links[] = '<span>'.$page.'</span>';
            else $links[] = $this->link($page);
        }
        
        if ($this->show_anchors && ($wp_last = $window_pages[$wp_count-1]) != $last)
        {
            if (($last - $wp_last) > 1) $links[] = '...';
            $links[] = $this->link($last);
        }
    
        return implode($this->separator, $links);
    }
    
    protected function link($page)
    {
        return link_to($page, array_merge($this->params, array($this->param_name => $page)), $this->html_options);
    }
}

?>
