<?= tiny_mce_init('post_content'); ?>
<div id="form">
    <p>
        <label for="post_title">Title</label>
        <?= text_field('post', 'title', $this->post, array('size' => 80)); ?>
        <?= link_to_function('Editer le lien permanent', "Toggle.display('permalink')"); ?>
    </p>
    <p id="permalink" style="display:none;">
        <label for="post_permalink">Lien permanent</label>
        <?= text_field('post', 'permalink', $this->post, array('size' => 80)); ?>
    </p>
    <p>
        <label for="post_published"><?= check_box('post', 'published', $this->post); ?>&nbsp;En ligne ?</label>
    </p>
    <p>
        <label for="post_teaser">Chapeau</label>
        <?= text_area('post', 'teaser', $this->post); ?>
    </p>
    <p>
        <label for="post_content">Contenu</label>
        <?= text_area('post', 'content', $this->post); ?>
    </p>
    <p>
        <label for="post_created_on">Date de création</label>
        <?= date_time_select('post', 'created_on', $this->post); ?>
    </p>
</div>
