<h2>Actualités</h2>
<h3>Nouvelle actu</h3>
<p id="actions">
    <?= link_to('Retour', array('action' => 'index'), array('class' => 'action back')); ?>
</p>
<?= form_tag(array('action' => 'create')); ?>
    <?= error_message_for('post', $this->post); ?>
    <?= $this->render_partial('form'); ?>
    <?= submit_tag('Enregister l\'actu'); ?> ou <?= link_to('Annuler', array('action' => 'index')); ?>
<?= end_form_tag(); ?>
