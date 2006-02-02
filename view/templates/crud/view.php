<h2>Détail</h2>
<p><?= link_to('Retour', array('action' => 'index')); ?></p>
<dl>
<? foreach($this->entity->contentAttributes() as $attr) { ?>
    <dt><?= ucfirst($attr); ?></dt>
    <dd><?= $this->entity->$attr; ?></dd>
<? } ?>
</dl>
<p><?= link_to('Retour', array('action' => 'index')); ?></p>
