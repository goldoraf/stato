<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Administration du site FDA</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?= javascript_include_tag(array('ext-1.1.1/adapter/ext/ext-base.js', 'ext-1.1.1/ext-all-debug.js', 'application')); ?>
<script type="text/javascript">
    /*$(document).ready(function() {
        $('#notice').fadeIn('slow');
        window.setTimeout("$('#notice').fadeOut('slow')", 4000);
    });*/
</script>
<link rel="stylesheet" type="text/css" media="screen" href="/js/ext-1.1.1/resources/css/ext-all.css" />
<?= stylesheet_link_tag('admin'); ?>
</head>
<body>
    <div id="container">
        <div id="header">
            <span id="logo">
                
            </span>
            <div id="info">
                <?= link_to('Configuration', array('controller' => 'admin/settings', 'action' => 'index'), array('class' => 'action config')); ?> |
                <?= link_to('Déconnexion', array('controller' => 'login', 'action' => 'logout'), array('class' => 'action logout')); ?>
            </div>
            <div id="notice" style="display:none;">
                <? if (isset($this->flash['notice'])) echo '<p>'.$this->flash['notice'].'</p>'; ?>
            </div>
            <div id="navigation">
                <ul>
                    <li id="nav-first-link"><?= link_to('Actualités', array('controller' => 'admin/posts')); ?></li>
                    <li><?= link_to('Pages', array('controller' => 'admin/pages')); ?></li>
                    <li><?= link_to('Fichiers', array('controller' => 'admin/files')); ?></li>
                    <li><?= link_to('Centres', array('controller' => 'admin/centres_exam')); ?></li>
                    <li id="nav-last-link"><?= link_to('Utilisateurs', array('controller' => 'admin/users')); ?></li>
                </ul>
            </div>
        </div>
        
        <div id="main">
            <?= $this->layout_content; ?>
        </div>
        
        <div id="footer">
            CCIP - DRI/E
        </div>
    </div>  
</body>
</html>
