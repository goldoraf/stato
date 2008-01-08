<h2>Pages</h2>
<h3>Création d'une nouvelle page</h3>
<p id="actions">
    <?= link_to('Retour', array('action' => 'index'), array('class' => 'action back')); ?>
</p>
<?= form_tag(array('action' => 'create')); ?>
    <?= $this->render_partial('form'); ?>
    <?= submit_tag('Enregister la page'); ?> ou <?= link_to('Annuler', array('action' => 'index')); ?>
<?= end_form_tag(); ?>

<?= $this->render_partial('form_dialogs'); ?>
