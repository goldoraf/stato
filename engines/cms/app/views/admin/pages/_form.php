<script type="text/javascript">
Ext.onReady(function(){
    var editor = new Ext.ux.CustomHtmlEditor({
        id:'page_content',
        width:750,
        height:400
    });
    editor.applyTo('page_content');
})
</script>
<div id="form">
    <?= error_message_for('page', $this->page); ?>
    <div>
        <label for="page_parent_id">Page parente</label>
        <?= select_tag('page[parent_id]', page_tree_for_select(Page::$objects->roots(), $this->page)); ?>
    </div>
    <div>
        <label for="page_title">Titre</label>
        <?= text_field('page', 'title', $this->page, array('size' => 80)); ?>
        <?= link_to_function('Editer le lien permanent', "Toggle.display('slug')"); ?>
    </div>
    <div id="slug" style="display:none;">
        <label for="page_slug">Lien permanent</label>
        <?= text_field('page', 'slug', $this->page); ?>
    </div>
    <!-- IMPORTANT : si l'on intègre le label et le textarea dans un paragraphe,
         IE bugge lors de la création de l'éditeur HTML -->
    <div>
        <label for="page_content">Contenu</label>
        <?= text_area('page', 'content', $this->page); ?>
    </div>
    <div>
        <label for="page_published"><?= check_box('page', 'published', $this->page); ?>&nbsp;En ligne ?</label>
    </div>
    <div>
        <label for="page_created_on">Date de création</label>
        <?= date_time_select('page', 'created_on', $this->page); ?>
    </div>
</div>
