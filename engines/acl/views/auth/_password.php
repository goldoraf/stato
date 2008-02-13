<div class="user-password">
    <? $f = new SFormBuilder('user', $this->user); ?>
    <p>
        <?= $f->label('password', __('Password')); ?>
        <?= $f->password_field('password', array('size' => 30)); ?>
    </p>
    <p>
        <?= $f->label('password_confirmation', __('Password Confirmation')); ?>
        <?= $f->password_field('password_confirmation', array('size' => 30)); ?>
    </p>
</div>
