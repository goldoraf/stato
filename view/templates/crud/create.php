<h1>New <?= $this->singular_name; ?></h1>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
<?= error_message_for($this->class_name); ?>
<?= form($this->class_name, array('action' => 'create')); ?>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
