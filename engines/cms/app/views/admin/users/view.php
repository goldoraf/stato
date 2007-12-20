<h1>View User</h1>
<p>
    <?= link_to('Edit', array('action' => 'update', 'id' => $this->user->id)); ?> | 
    <?= link_to('Back', array('action' => 'index')); ?>
</p>
<? foreach ($this->user->content_attributes() as $attr) : ?>
    <p>
        <b><?= SInflection::humanize($attr->name); ?> : </b>
        <?= $this->user->{$attr->name}; ?>
    </p>
<? endforeach; ?>
<p>
    <?= link_to('Edit', array('action' => 'update', 'id' => $this->user->id)); ?> | 
    <?= link_to('Back', array('action' => 'index')); ?>
</p>

