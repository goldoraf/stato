<div id="page-content">
    <h2>Demande de renseignements</h2>
    <p>Tous les champs sont obligatoires.</p>
    <?= form_tag(array('action' => 'contact')); ?>
        <?= error_message_for('request', $this->request_for_info); ?>
        <p>
            <label for="request_name">Nom et prénom</label>
            <?= text_field('request', 'name', $this->request_for_info, array('size' => 80)); ?>
        </p>
        <p>
            <label for="request_email">Courriel</label>
            <?= text_field('request', 'email', $this->request_for_info, array('size' => 80)); ?>
        </p>
        <p>
            <label for="request_subject">Sujet</label>
            <?= text_field('request', 'subject', $this->request_for_info, array('size' => 80)); ?>
        </p>
        <p>
            <label for="request_body">Contenu</label>
            <?= text_area('request', 'body', $this->request_for_info, array('cols' => 60)); ?>
        </p>
        <?= submit_tag('Confirmer'); ?> ou <?= link_to('Annuler', home_url()); ?>
    <?= end_form_tag(); ?>
    <p>Vous disposez d'un droit d'accès, de modification, de rectification et de 
    suppression des données qui vous concernent (art.34 de la loi "Informatique et Libertés"). 
    Pour l'exercer, adressez-vous à cpdp@ccip.fr.
La CCIP est seule destinataire des informations.</p>
</div>
<div id="secondary-nav">
    <?= $this->render_partial('contact'); ?>
</div>
