<h2><?= __('Listing users'); ?></h2>
<table class="object-list">
    <thead>
        <tr>
            <th><?= __('ID'); ?></th>
            <th><?= __('Last Name'); ?></th>
            <th><?= __('First Name'); ?></th>
            <th><?= __('Login'); ?></th>
            <th><?= __('Email'); ?></th>
            <th><?= __('Last Login'); ?></th>
            <th />
        </tr>
    </thead>
    <tbody>
        <? foreach ($this->users as $u) : ?>
        <tr>
            <td><?= $u->id; ?></td>
            <td><?= $u->lastname; ?></td>
            <td><?= $u->firstname; ?></td>
            <td><?= $u->login; ?></td>
            <td><?= $u->email; ?></td>
            <td><?= ($u->logged_in_on === null) ? '-' : $u->logged_in_on->localize(); ?></td>
            <td class="actions">
                <?= link_if_authorized(__('Show'), $this->current_user,
                                       array('action' => 'show', 'id' => $u->id)); ?>
                <?= link_if_authorized(__('Edit'), $this->current_user,
                                       array('action' => 'edit', 'id' => $u->id)); ?>
                <? if (is_authorized($this->current_user, array('action' => 'delete'))
                       && $u->login != AclEngine::config('admin_login')) : ?>
                    <?= button_to(__('Delete'), array('action' => 'delete', 'id' => $u->id),
                                  array('confirm' => 'Are you sure ?')); ?>
                <? endif; ?>
            </td>
        </tr>
        <? endforeach; ?>
    </tbody>
</table>
<div class="pagination-links">
    <?= pagination_links($this->users_pages); ?>
</div>
<div class="actions">
    <?= link_if_authorized(__('New User'), $this->current_user, array('action' => 'create')); ?>
</div>
