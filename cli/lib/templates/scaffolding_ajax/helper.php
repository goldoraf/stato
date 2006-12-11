<?php

function sort_link_class($param)
{
    $global_params = SUrlRewriter::current_params();
    if (isset($global_params['sort']))
    {
        if ($global_params['sort'] == $param) return 'sortup';
        if ($global_params['sort'] == $param.'_reverse') return 'sortdown';
    }
}

function remote_sort_link($text, $param, $action = 'index')
{
    $global_params = SUrlRewriter::current_params();
    $key = $param;
    if (isset($global_params['sort']) && $global_params['sort'] == $param) $key.= '_reverse';
    
    $options = array(
        'url' => array_merge($global_params, array('page' => 1, 'sort' => $key)),
        'update' => 'table',
        'before' => "Element.show('loading')",
        'success' => "Element.hide('loading')",
    );
    $html_options = array(
        'class' => sort_link_class($param),
        'href'  => url_for(array_merge($global_params, array('page' => 1, 'sort' => $key)))
    );
    
    return link_to_remote($text, $options, $html_options);
}

function remote_server_link($object)
{
    $options = array(
        'url' => array('action' => 'index', 'object' => $object),
        'update' => 'partial',
        'before' => "Element.show('loading')",
        'success' => "Element.hide('loading')",
    );
    $html_options = array(
        'href'  => url_for(array('action' => 'index', 'object' => $object))
    );
    
    return link_to_remote(ucfirst($object), $options, $html_options);
}

function remote_pagination_links($paginator, $options = array(), $html_options = array())
{
    $options['params'] = SUrlRewriter::current_params();
    $p = new RemotePaginationHelper($paginator, $options, $html_options);
    return $p->links();
}

class RemotePaginationHelper extends SPaginationHelper
{
    protected function link($page)
    {
        $options = array
        (
            'url' => array_merge($this->params, array('action' => 'index', $this->param_name => $page)),
            'update' => 'table',
            'before' => "Element.show('loading')",
            'success' => "Element.hide('loading')"
        );
        $html_options = array(
            'href' => url_for(array_merge($this->params, array('action' => 'index', $this->param_name => $page))),  
        );
        return link_to_remote($page, $options, $html_options);
    }
}

?>
