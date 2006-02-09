<h1>New <?= $this->singular_name; ?></h1>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
<?= error_message_for($this->singular_name); ?>
<?= form($this->singular_name, array('action' => 'create')); ?>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
