<h1>View Centre exam</h1>
<p>
    <?= link_to('Edit', array('action' => 'update', 'id' => $this->centre_exam->id)); ?> | 
    <?= link_to('Back', array('action' => 'index')); ?>
</p>
<? foreach ($this->centre_exam->content_attributes() as $attr) : ?>
    <p>
        <b><?= SInflection::humanize($attr->name); ?> : </b>
        <?= $this->centre_exam->{$attr->name}; ?>
    </p>
<? endforeach; ?>
<p>
    <?= link_to('Edit', array('action' => 'update', 'id' => $this->centre_exam->id)); ?> | 
    <?= link_to('Back', array('action' => 'index')); ?>
</p>

