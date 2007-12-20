<h2>Centres d'examens</h2>
<h3>Importer à partir d'un fichier</h3>
<p id="actions">
    <?= link_to('Retour', array('action' => 'index'), array('class' => 'action back')); ?>
</p>
<?= form_tag(array('action' => 'import'), array('multipart' => true)); ?>
    <div id="form">
        <p>
            <label for="file">Fichier CSV</label>
            <?= file_field_tag('file'); ?>
        </p>
    </div>
    <?= submit_tag('Importer le fichier'); ?> ou <?= link_to('Annuler', array('action' => 'index')); ?>
<?= end_form_tag(); ?>
