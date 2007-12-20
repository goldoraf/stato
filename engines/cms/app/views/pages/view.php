<div id="page-content">
    <?= $this->content_page->content; ?>
</div>
<?= $this->render_partial('secondary_nav', array('root_page' => $this->content_page->root())); ?>
