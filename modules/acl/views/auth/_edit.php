<div class="user-edit">
    <? $f = new SFormBuilder('user', $this->user); ?>
    <p>
        <?= $f->label('firstname', __('First Name')); ?>
        <?= $f->text_field('firstname'); ?>
    </p>
    <p>
        <?= $f->label('lastname', __('Last Name')); ?>
        <?= $f->text_field('lastname'); ?>
    </p>
    <p>
        <?= $f->label('login', __('Login ID')); ?>
        <?= $f->text_field('login', array('size' => 30)); ?>
    </p>
    <p>
        <?= $f->label('email', __('Email')); ?>
        <?= $f->text_field('email'); ?>
    </p>
</div>
