<h2>Création d'un nouvel objet de type '<?php echo $entity_class; ?>'</h2>
<p><?php echo link_to('Retour', array('action' => 'index')); ?></p>
<?php echo error_message_for($entity_class); ?>
<?php echo form($entity_class); ?>
<p><?php echo link_to('Retour', array('action' => 'index')); ?></p>
