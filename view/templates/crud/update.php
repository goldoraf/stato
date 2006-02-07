<h1>Editing <?= $this->singular_name; ?></h1>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
<?= error_message_for($this->class_name); ?>
<?= form($this->class_name, array('action' => 'update')); ?>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
