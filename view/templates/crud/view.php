<h1>View <?= $this->singular_name; ?></h1>
<p>
    <?= link_to('Edit', array('action' => 'update', 'id' => $this->entity->id)); ?> | 
    <?= link_to('Back', array('action' => 'index')); ?>
</p>
<? foreach($this->entity->contentAttributes() as $attr) : ?>
    <p>
        <b><?= ucfirst($attr); ?> : </b>
        <?= $this->entity->$attr; ?>
    </p>
<? endforeach; ?>
<p>
    <?= link_to('Edit', array('action' => 'update', 'id' => $this->entity->id)); ?> | 
    <?= link_to('Back', array('action' => 'index')); ?>
</p>

