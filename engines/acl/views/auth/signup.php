<div class="form">
    <h3><?= __('Signup'); ?></h3>
    
    <?= error_message_for('user', $this->user); ?>
    
    <div class="form-padding">
        <?= form_tag(array('action'=>'signup')); ?>
            <?= $this->render_partial('edit'); ?>
            <?= $this->render_partial('password'); ?>
            
            <div class="button-bar">
                <?= submit_tag(__('Signup')); ?>
                <?= link_to(__('Cancel'), array('action' => 'login')); ?>
            </div>
        </form>
    </div>
</div>
