<div class="user-details">
    <h2><?= __('User:'); ?> <?= $this->user->login; ?></h2>
    <p>
        <b><?= __('ID'); ?></b> <?= $this->user->id; ?>
    </p>
    <p>
        <b><?= __('Last Name'); ?></b> <?=h $this->user->lastname; ?>
    </p>
    <p>
        <b><?= __('First Name'); ?></b> <?=h $this->user->firstname; ?>
    </p>
    <p>
        <b><?= __('Login'); ?></b> <?=h $this->user->login; ?>
    </p>
    <p>
        <b><?= __('Email'); ?></b> <?=h $this->user->email; ?>
    </p>
    <p>
        <b><?= __('Last Login'); ?></b> <?= $this->user->logged_in_on; ?>
    </p>
    <p>
        <b><?= __('Created On'); ?></b> <?= $this->user->created_on; ?>
    </p>
    
    <h2>Roles</h2>
    <ul id="user-role-list">
      <? foreach ($this->user->roles->all() as $role) : ?>
      <?= link_if_authorized($role->name, $this->current_user, array('controller' => 'admin/roles', 'action' => 'show', 'id' => $role->id), array('wrap_in' => 'li', 'show_text' => true)); ?>
      <? endforeach; ?>
    </ul>
</div>
    
<ul class="actions">
<?= link_if_authorized(__('Edit'), $this->current_user, array('action' => 'edit', 'id' => $this->user->id), array('wrap_in' => 'li')); ?>
<?= link_if_authorized(__('Back'), $this->current_user, array('action' => 'index'), array('wrap_in' => 'li')); ?>
</ul>