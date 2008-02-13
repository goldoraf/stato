<div id="login" class="form">
    <h3><?= __('Please Login'); ?></h3>
    
    <div class="form-padding">
        <?= form_tag(array('action'=>'login')); ?>
            <p>
                <label for="login"><?= __('Login'); ?></label>
                <?= text_field_tag('login', null, array('size' => 30)); ?>
            </p>
            <p>
                <label for="password"><?= __('Password'); ?></label>
                <?= password_field_tag('password', null, array('size' => 30)); ?>
            </p>
            
            <div class="button-bar">
                <?= submit_tag('Ok'); ?>
                <?= link_to(__('Register for an account'), array('action' => 'signup')); ?> |
                <?= link_to(__('Forgot my password'), array('action' => 'forgot_password')); ?>
            </div>
        </form>
    </div>
</div>
