<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Authentification</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?= stylesheet_link_tag('login'); ?>
</head>
<body>
    <div id="loginbox">
        <div>
            <h1>Authentification</h1>
            <?= form_tag(array('controller' => 'login', 'action' => 'authenticate')); ?>
                <p>
                    <label>Identifiant</label>
                    <?= text_field_tag('login', null, array('size' => 30)); ?>
                </p>
                <p>
                    <label>Mot de passe</label>
                    <?= password_field_tag('password', null, array('size' => 30)); ?>
                </p>
                <? if ($this->flash['notice'] !== null) : ?>
                    <p class="form-errors"><?= $this->flash['notice']; ?></p>
                <? endif; ?>
                <p style="text-align: right">
                    <?= submit_tag('Ok'); ?>
                </p>
            </form>
		</div>
    </div>
</body>
</html>
