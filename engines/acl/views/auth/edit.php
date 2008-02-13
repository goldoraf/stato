<div class="form">
    <h3><?= __('Edit user'); ?></h3>
    
    <?= error_message_for('user', $this->user); ?>
    
    <div class="form-padding">
        <?= form_tag(array('action'=>'edit')); ?>
            <?= $this->render_partial('edit'); ?>
            <div class="button-bar">
                <?= submit_tag(__('Change Settings')); ?>
            </div>
        </form>
    </div>
    <div class="form-padding">
        <?= form_tag(array('action'=>'change_password')); ?>
            <?= hidden_field_tag('return_to', url_for(array('action' => 'edit'))); ?>
            <?= $this->render_partial('password'); ?>
            <div class="button-bar">
                <?= submit_tag(__('Change Password')); ?>
            </div>
        </form>
    </div>
    <div class="user-delete">
        <?= button_to(__('Delete Account'), array('action' => 'delete')); ?>
    </div>
</div>
