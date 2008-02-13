<div id="role-details">
    <h2><?= __('Role:'); ?> <?= $this->role->name; ?></h2>
    
    <p>
        <b><?= __('ID'); ?></b> <?= $this->role->id; ?>
    </p>
    <p>
        <b><?= __('Name'); ?></b> <?=h $this->role->name; ?>
    </p>
    <p>
        <b><?= __('Description'); ?></b> <?=h $this->role->description; ?>
    </p>
    <p>
        <b><?= __('Admin?'); ?></b> <?= ($this->role->omnipotent) ? __("Yes") : __("No"); ?>
    </p>
    
    <h3><?= __('Permissions'); ?></h3>
    <table id="role-permissions-table">
        <thead>
            <tr>
            <? foreach (array_keys($this->all_actions) as $controller_name) : ?>
                <th><?= $controller_name; ?></th>
            <? endforeach; ?>
            </tr>
        </head>
        <tbody>
            <tr>
            <? foreach (array_keys($this->all_actions) as $controller_name) : ?>
                <td><ul>
                <? foreach ($this->all_actions[$controller_name] as $perm) : ?>
                    <li>
                        <strong><?= link_if_authorized($perm->action, $this->current_user, array('controller' => 'admin/permissions', 'action' => 'show', 'id' => $perm->id), array('show_text' => true)); ?></strong><br />
                        <?= $perm->description; ?>
                    </li>
                <? endforeach; ?>
                </ul></td>
            <? endforeach; ?>
        </tr></tbody>
    </table>
    
    <h3><?= __('Users'); ?></h3>
    <ul id="role-users-list">
        <? foreach ($this->role->users->all() as $user) : ?>
            <?= link_if_authorized($user->__repr(), $this->current_user, array('controller' => 'admin/users', 'action' => 'show', 'id' => $user->id), array('wrap_in' => 'li', 'show_text' => true)); ?>
        <? endforeach; ?>
    </ul>
</div>

<ul class="actions">
<?= link_if_authorized(__('Edit'), $this->current_user, array('action' => 'edit', 'id' => $this->role->id), array('wrap_in' => 'li')); ?>
<?= link_if_authorized(__('Back'), $this->current_user, array('action' => 'index'), array('wrap_in' => 'li')); ?>
</ul>