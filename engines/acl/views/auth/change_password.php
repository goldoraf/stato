<div class="form">
    <h3><?= __('Change Password'); ?></h3>
    
    <?= error_message_for('user', $this->user); ?>
    
    <div class="form-padding">
        <p><?= __("Enter your new password in the fields below and click 'Change Password'. Your new password will be sent to your email inbox."); ?></p>
        <?= form_tag(array('action'=>'change_password')); ?>
            <?= $this->render_partial('password'); ?>
            
            <div class="button-bar">
                <?= submit_tag(__('Change Password')); ?>
                <?= link_to(__('Cancel'), array('action' => 'home')); ?>
            </div>
        </form>
    </div>
</div>
