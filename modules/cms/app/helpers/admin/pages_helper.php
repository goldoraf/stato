<?php

function page_tree_view($roots_pages, $page_helper = 'page_tree_row', $selected = null, $level = 0, $parent_page_number = '')
{
    $tree = '';
    $count = 1;
    foreach ($roots_pages as $root_page)
    {
        $page_number = $parent_page_number.$count.'.';
        $tree.= $page_helper($root_page, $selected, $level, $page_number);
        if ($root_page->children->count() != 0)
            $tree.= page_tree_view($root_page->children->all(), $page_helper, $selected, $level + 1, $page_number);
        $count++;
    }    
    return $tree;
}

function page_tree_row($page, $selected, $level, $page_number)
{
    return 
        content_tag('tr',
            content_tag('td', $page_number)
            .content_tag('td', str_repeat('-&nbsp;&nbsp;&nbsp;', $level)
                              .truncate($page->title, 70))
            .content_tag('td', link_to('Preview', array('action' => 'preview', 'id' => $page->id),
                                       array('popup' => true, 'class' => 'action preview')))
            .content_tag('td', $page->created_on->localize())
            .content_tag('td', (($page->published) ? image_tag('accept') : image_tag('disable')))
            .content_tag('td', link_to('Editer', array('action' => 'update', 'id' => $page->id), array('class' => 'action edit')))
            .content_tag('td', button_to('Supprimer', array('action' => 'delete', 'id' => $page->id),
                                         array('confirm' => 'Etes vous sûr(e) ?', 'class' => 'action delete')))
        );
}

function page_tree_for_select($roots_pages, $selected)
{
    return content_tag('option', 'Aucune', array('value' => ''), array('selected' => ($selected->parent_id === null)))
           .page_tree_view($roots_pages, 'page_tree_option', $selected);
}

function page_tree_option($page, $selected, $level, $page_number)
{
    return content_tag('option', $page_number.' - '.$page->title, 
                       array('value' => $page->id, 'selected' => ($page->id == $selected->parent_id)));
}

function page_tree_for_links($roots_pages, $level = 0)
{
    $tree = '';
    foreach ($roots_pages as $root_page)
    {
        if ($root_page->children->count() != 0)
            $tree.= content_tag('li', page_node_link().page_tree_link($root_page)
                                     .page_tree_for_links($root_page->children->all(), $level + 1));
        else
            $tree.= content_tag('li', '&nbsp;'.page_tree_link($root_page));
    }
    $html_options = array();
    if ($level != 0) $html_options['style'] = 'display: none;';
    return content_tag('ul', $tree, $html_options);
}

function page_tree_link($page)
{
    return link_to_function($page->title, "submit_url('".page_url(array('path' => $page->path))."')");
}

function page_node_link()
{
    return link_to_function('<span>+</span>', 'Toggle.display(this.parentNode.childNodes[2]);'
    .'this.className = (this.className == \'closed\') ? \'open\' : \'closed\'', array('class' => 'closed'));
}

?>
