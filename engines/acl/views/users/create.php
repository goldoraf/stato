<div class="form">
    <h3><?= __('New User'); ?></h3>
    
    <?= error_message_for('user', $this->user); ?>
    
    <div class="form-padding">
        <?= form_tag(array('action'=>'create')); ?>
            <?= $this->render_partial('user/edit'); ?>
            <?= $this->render_partial('user/password'); ?>
            
            <div class="button-bar">
                <?= submit_tag(__('Create User')); ?>
                <?= link_to(__('Cancel'), array('action' => 'index')); ?>
            </div>
        </form>
    </div>
</div>
