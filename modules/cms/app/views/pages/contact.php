<div id="page-content">
    <h2>Demande de contact</h2>
    <p>Tous les champs sont obligatoires.</p>
    <?= form_tag(array('action' => 'contact')); ?>
        <?= error_message_for('message', $this->message); ?>
        <p>
            <label for="message_name">Nom et prénom</label>
            <?= text_field('message', 'name', $this->message, array('size' => 80)); ?>
        </p>
        <p>
            <label for="message_email">Courriel</label>
            <?= text_field('message', 'email', $this->message, array('size' => 80)); ?>
        </p>
        <p>
            <label for="message_subject">Sujet</label>
            <?= text_field('message', 'subject', $this->message, array('size' => 80)); ?>
        </p>
        <p>
            <label for="message_body">Contenu</label>
            <?= text_area('message', 'body', $this->message, array('cols' => 60)); ?>
        </p>
        <?= submit_tag('Confirmer'); ?> ou <?= link_to('Annuler', home_url()); ?>
    <?= end_form_tag(); ?>
</div>
<div id="secondary-nav">
    
</div>
