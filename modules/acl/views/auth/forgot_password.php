<div class="form">
    <h3><?= __('Forgotten Password'); ?></h3>
    
    <div class="form-padding">
        <?= form_tag(array('action'=>'forgot_password')); ?>
            <p><?= __("Enter your email address in the field below and click 'Reset Password' to have a new password emailed to you."); ?></p>
            <p>
                <label for="email"><?= __('Email'); ?></label>
                <?= text_field_tag('email'); ?>
            </p>
            
            <div class="button-bar">
                <?= submit_tag(__('Reset Password')); ?>
                <?= link_to(__('Cancel'), array('action' => 'login')); ?>
            </div>
        </form>
    </div>
</div>
