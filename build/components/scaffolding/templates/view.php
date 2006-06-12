<h1>View <?= $this->singular_name; ?></h1>
<p>
    <?= link_to('Edit', array('action' => 'edit', 'id' => $this->{$this->singular_name}->id)); ?> | 
    <?= link_to('Back', array('action' => 'index')); ?>
</p>
<? foreach($this->{$this->singular_name}->contentAttributes() as $attr) : ?>
    <p>
        <b><?= SInflection::humanize($attr->name); ?> : </b>
        <?= $this->{$this->singular_name}->{$attr->name}; ?>
    </p>
<? endforeach; ?>
<p>
    <?= link_to('Edit', array('action' => 'edit', 'id' => $this->{$this->singular_name}->id)); ?> | 
    <?= link_to('Back', array('action' => 'index')); ?>
</p>

