<div class="form">
    <h2><?= __('New role'); ?></h2>
    <div class="form-padding">
        <?= form_tag(array('action'=>'create')); ?>
            <?= $this->render_partial('form'); ?>
            <div class="button-bar">
                <?= submit_tag(__('Create')); ?>
            </div>
        </form>
    </div>
</div>

<ul class="actions">
<?= link_if_authorized(__('Back'), $this->current_user, array('action' => 'index'), array('wrap_in' => 'li')); ?>
</ul>