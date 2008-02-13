<h2>Centres d'examens</h2>
<h3>Création d'un centre</h3>
<p id="actions">
    <?= link_to('Retour', array('action' => 'index'), array('class' => 'action back')); ?>
</p>
<?= form_tag(array('action' => 'create')); ?>
    <?= error_message_for('centre_exam', $this->centre_exam); ?>
    <?= $this->render_partial('form'); ?>
    <?= submit_tag('Enregistrer'); ?> ou <?= link_to('Annuler', array('action' => 'index')); ?>
<?= end_form_tag(); ?>
