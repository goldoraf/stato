<div id="secondary-nav">
    <h3>En savoir plus</h3>
    <ul>
        <span><?= cms_page_link($root_page); ?></span>
        <? foreach ($root_page->published_children() as $child_page) : ?>
            <li><?= cms_page_link($child_page); ?></li>
        <? endforeach; ?>
        
        <? if ($root_page->full_path == 'tef') echo content_tag('li', link_to('Centres agréés', array('action' => 'centres_tef'))); ?>
        <? if ($root_page->full_path == 'examens') echo content_tag('li', link_to('Centres agréés', array('action' => 'centres_exam'))); ?>
    </ul>
</div>
