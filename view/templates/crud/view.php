<h2>Détail</h2>
<p><?php echo link_to('Retour', array('action' => 'index')); ?></p>
<dl>
<?php foreach($entity->contentAttributes() as $attr) { ?>
    <dt><?php echo ucfirst($attr); ?></dt>
    <dd><?php echo $entity->$attr; ?></dd>
<?php } ?>
</dl>
<p><?php echo link_to('Retour', array('action' => 'index')); ?></p>
