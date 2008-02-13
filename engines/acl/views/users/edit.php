<div class="form">
    <h2><?= __('Editing user'); ?> '<?= $this->user->login; ?>'</h2>
    
    <?= error_message_for('user', $this->user); ?>
    
    <? if (is_authorized($this->current_user, array('action'=>'edit'))) : ?>
        <div class="form-padding">
            <?= form_tag(array('action'=>'edit', 'id'=>$this->user->id)); ?>
                <?= $this->render_partial('user/edit'); ?>
                <div class="button-bar">
                    <?= submit_tag(__('Change Settings')); ?>
                </div>
            </form>
        </div>
    <? endif; ?>
    
    <h3><?= __('Password'); ?></h3>
    <? if (is_authorized($this->current_user, array('action'=>'change_password'))) : ?>
        <div class="form-padding">
            <?= form_tag(array('action'=>'change_password', 'id'=>$this->user->id)); ?>
                <?= hidden_field_tag('return_to', url_for(array('action' => 'edit'))); ?>
                <?= $this->render_partial('user/password'); ?>
                <div class="button-bar">
                    <?= submit_tag(__('Change Password')); ?>
                </div>
            </form>
        </div>
    <? endif; ?>
    
    <h3><?= __('Roles'); ?></h3>
    <? if (is_authorized($this->current_user, array('action'=>'edit_roles'))) : ?>
        <div class="form-padding">
            <?= form_tag(array('action'=>'edit_roles', 'id'=>$this->user->id)); ?>
                <?= $this->render_partial('roles'); ?>
                <div class="button-bar">
                    <?= submit_tag(__('Save Roles')); ?>
                </div>
            </form>
        </div>
    <? else : ?>
        <ul id="user-role-list">
            <? foreach ($this->user->roles->all() as $role) : ?>
            <li><?= $role->name.' - '.$role->description; ?></li>
            <? endforeach; ?>
        </ul>
    <? endif; ?>
    
    <div id="user-delete">
        <?= button_to(__('Delete Account'), array('action' => 'delete', 'id' => $this->user->id)); ?>
    </div>
</div>

<ul class="actions">
<?= link_if_authorized(__('Show'), $this->current_user, array('action' => 'show', 'id' => $this->user->id), array('wrap_in' => 'li')); ?>
<?= link_if_authorized(__('Back'), $this->current_user, array('action' => 'index'), array('wrap_in' => 'li')); ?>
</ul>
