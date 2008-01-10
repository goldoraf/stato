<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Administration du site : <?= config_value('site_name'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">
    /*$(document).ready(function() {
        $('#notice').fadeIn('slow');
        window.setTimeout("$('#notice').fadeOut('slow')", 4000);
    });*/
</script>
<link rel="stylesheet" type="text/css" media="screen" href="<?= compute_public_path('ext-2.0/resources/css/ext-all.css', 'js'); ?>" />
<?= stylesheet_link_tag('admin'); ?>
</head>
<body>
    <div id="loading-mask" style=""></div>
    <div id="loading">
        <div class="loading-indicator"><!--<img src="resources/extanim32.gif" width="32" height="32" style="margin-right:8px;" align="absmiddle"/>-->Loading...</div>
    </div>
    <!-- include everything after the loading indicator -->
    <?= javascript_include_tag(array('ext-2.0/adapter/ext/ext-base.js', 'ext-2.0/ext-all-debug.js', 'application_extjs2', 'CustomHtmlEditor')); ?>
    <?= javascript_tag("Ext.BLANK_IMAGE_URL = '".compute_public_path('ext-2.0/resources/images/default/s.gif', 'js')."';"); ?>
    <?= javascript_tag("StatoCms.BASE_URI = '/d3/admin';"); ?>
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
                <li id="nav-last-link"><?= link_to('Utilisateurs', array('controller' => 'admin/users')); ?></li>
            </ul>
        </div>
    </div>
        
    <?= $this->layout_content; ?>
        
    <div id="footer">
        Fueled by <a href="http://www.stato-framework.org">Stato</a>
    </div> 
</body>
</html>
