<ul>
    <? foreach ($this->all_roles as $role) : ?>
        <li>
            <input type="checkbox" id="user_roles_<?= $role->id; ?>" name="user[roles][]" value="<?= $role->id; ?>" 
              <? if ($this->user->roles->is_included($role)) : ?>
                checked="checked"
              <? endif; ?>
            />
            <?= link_if_authorized($role->name, $this->current_user, array('controller' => 'admin/roles', 'action' => 'show', 'id' => $role->id), array('show_text' => true)); ?>
            &raquo;
            <?=h $role->description; ?>
    <? endforeach; ?>
</ul>