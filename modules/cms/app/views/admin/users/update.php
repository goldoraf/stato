<h1>Editing User</h1>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
<?= form_tag(array('action' => 'update')); ?>
    <?= error_message_for('user', $this->user); ?>
    <?= hidden_field('user', 'id', $this->user); ?>
    <?= $this->render_partial('form'); ?>
    <?= submit_tag('Update'); ?>
<?= end_form_tag(); ?>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
