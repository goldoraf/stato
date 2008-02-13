<h2>Actualités</h2>
<h3>Edition d'une actu</h3>
<p id="actions">
    <?= link_to('Retour', array('action' => 'index'), array('class' => 'action back')); ?>
    <?= link_to('Preview', array('action' => 'preview', 'id' => $this->post->id), array('class' => 'action preview')); ?>
</p>
<?= form_tag(array('action' => 'update')); ?>
    <?= error_message_for('post', $this->post); ?>
    <?= hidden_field('post', 'id', $this->post); ?>
    <?= $this->render_partial('form'); ?>
    <?= submit_tag('Enregister l\'actu'); ?> ou <?= link_to('Annuler', array('action' => 'index')); ?>
<?= end_form_tag(); ?>
