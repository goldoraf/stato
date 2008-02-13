<?= error_message_for('role', $this->role); ?>

<div id="role-details">
    <? $f = new SFormBuilder('role', $this->role); ?>
    <p>
        <?= $f->label('name', __('Name')); ?>
        <?= $f->text_field('name'); ?>
    </p>
    <p>
        <?= $f->label('description', __('Description')); ?>
        <?= $f->text_area('description', array('rows' => 5)); ?>
    </p>
</div>
<script type="text/javascript">
function checkAll(name)
{
  boxes = document.getElementsByName(name)
  for (i = 0; i < boxes.length; i++)
        boxes[i].checked = true ;
}

function uncheckAll(name)
{
  boxes = document.getElementsByName(name)
  for (i = 0; i < boxes.length; i++)
        boxes[i].checked = false ;
}
</script>
<div id="role-permissions">
    <h3><?= __('Permissions'); ?></h3>
    <table id="role-permissions-table">
        <thead>
            <tr>
            <? foreach (array_keys($this->all_actions) as $controller_name) : ?>
                <? $controller_id = str_replace('/', '_', $controller_name); ?>
                <th><?= $controller_name; ?>
                <ul>
                    <li><a href="#" onclick="checkAll('permissions_<?= $controller_id; ?>[]'); return false;">all</a></li>
                    <li><a href="#" onclick="uncheckAll('permissions_<?= $controller_id; ?>[]'); return false;">none</a></li>
                </ul>
                </th>
            <? endforeach; ?>
            </tr>
        </head>
        <tbody>
            <tr>
            <? foreach (array_keys($this->all_actions) as $controller_name) : ?>
                <? $controller_id = str_replace('/', '_', $controller_name); ?>
                <td><ul>
                <? foreach ($this->all_actions[$controller_name] as $perm) : ?>
                    <li>
                        <input type="checkbox" id="<?= $controller_id."_".$perm->action; ?>" name="permissions_<?= $controller_id; ?>[]"
                               value="<?= $controller_id; ?>"<? if ($this->role->permissions->is_included($perm)) echo ' checked="checked"'; ?> />
                        <?= link_if_authorized($perm->action, $this->current_user, array('controller' => 'admin/permissions', 'action' => 'show', 'id' => $perm->id), array('show_text' => true)); ?>
                    </li>
                <? endforeach; ?>
                </ul></td>
            <? endforeach; ?>
        </tr></tbody>
    </table>
</div>