<div id="column-g">
    <div id="actu">
        <h2><?= $this->post->title; ?></h2>
        <p class="actu-date"><strong><?= $this->post->created_on->format(SLocale::translate('FORMAT_DATE')); ?></strong></p>
        <?= $this->post->content; ?>
    </div>
</div>
<div id="column-d">
    <?= $this->render_partial('actus'); ?>
</div>
