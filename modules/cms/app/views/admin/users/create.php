<h1>New User</h1>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
<?= form_tag(array('action' => 'create')); ?>
    <?= error_message_for('user', $this->user); ?>
    <?= $this->render_partial('form'); ?>
    <?= submit_tag('Create'); ?>
<?= end_form_tag(); ?>
<p><?= link_to('Back', array('action' => 'index')); ?></p>
