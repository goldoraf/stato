<h2>Création d'un nouvel objet de type '<?= $this->entity_class; ?>'</h2>
<p><?= link_to('Retour', array('action' => 'index')); ?></p>
<?= error_message_for($this->entity_class); ?>
<?= form($this->entity_class); ?>
<p><?= link_to('Retour', array('action' => 'index')); ?></p>
