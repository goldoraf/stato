<div id="secondary-nav">
    <h3>En savoir plus</h3>
    <ul>
        <span><?= cms_page_link($root_page); ?></span>
        <? foreach ($root_page->published_children() as $child_page) : ?>
            <li><?= cms_page_link($child_page); ?></li>
        <? endforeach; ?>
        
    </ul>
</div>
