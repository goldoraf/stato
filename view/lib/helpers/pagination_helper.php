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
function pager($paginator, $options = array(), $htmlOptions = array())
{
    $p = new SPaginationHelper($paginator, $options, $htmlOptions);
    return $p->links();
}

class SPaginationHelper
{
    protected $paginator   = null;
    protected $paramName   = 'page';
    protected $windowSize  = 2;
    protected $showAnchors = true;
    protected $params      = array();
    protected $htmlOptions = array();
    protected $linkToCurrentPage = array();
    
    public function __construct($paginator, $options = array(), $htmlOptions = array())
    {
        $this->paginator = $paginator;
        $this->htmlOptions = $htmlOptions;
        
        if (isset($options['param_name']))   $this->paramName = $options['param_name'];
        if (isset($options['window_size']))  $this->windowSize = $options['window_size'];
        if (isset($options['show_anchors'])) $this->showAnchors = $options['show_anchors'];
        if (isset($options['params']))       $this->params = $options['params'];
        
        if (isset($options['link_to_current_page']))
            $this->linkToCurrentPage = $options['link_to_current_page'];
    }
    
    public function links()
    {
        $currentPage = $this->paginator->currentPage;
        $pageCount   = $this->paginator->pageCount();
        $windowPages = $this->paginator->windowPages($this->windowSize);
        $wpCount     = count($windowPages);
        
        $first = 1;
        $last  = $pageCount;
        
        $html = '';
        
        if ($this->showAnchors && ($wpFirst = $windowPages[0]) != $first)
        {
            $html.= $this->link($first);
            if (($wpFirst - $first) > 1) $html.= '...';
            $html.= ' ';
        }
        
        foreach ($windowPages as $page)
        {
            if ($currentPage == $page && !$this->linkToCurrentPage) $html.= $page;
            else $html.= $this->link($page);
            $html.= ' ';
        }
        
        if ($this->showAnchors && ($wpLast = $windowPages[$wpCount-1]) != $last)
        {
            if (($last - $wpLast) > 1) $html.= '...';
            $html.= $this->link($last);
        }
    
        return $html;
    }
    
    protected function link($page)
    {
        return link_to($page, array_merge($this->params, array($this->paramName => $page)), $this->htmlOptions);
    }
}

?>
