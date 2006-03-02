<h1>Editing <?= $this->singular_name; ?></h1>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
<?= error_message_for($this->{$this->singular_name}); ?>
<?= form($this->singular_name, $this->{$this->singular_name}, array('action' => 'update')); ?>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
