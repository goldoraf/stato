<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?= config_value('site_name'); ?></title>
<meta name="description" content="<?= config_value('site_description'); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="fr" />
<?= stylesheet_link_tag(array('public')); ?>
<?= stylesheet_link_tag(array('print'), array('media' => 'print')); ?>
<?= css_ie_fix(); ?>
<link rel="alternate" type="application/rss+xml" title="Fil RSS" href="<?= rss_url(); ?>" />
<!--<link rel="icon" href="<?= image_path('favicon.ico'); ?>" type="image/x-icon" />-->
</head>
<body>
<div id="container">
    <div id="wrapper">
        <div id="main">
            <div id="header">
                <h1><?= config_value('site_title'); ?></h1>
                <div id="root-menu">
                    <ul>
                        <li><?= cms_link_to_unless_current_page('Accueil', home_url()); ?></li>
                    </ul>
                </div>
            </div>
            
            <div id="main-content">
                <?= $this->layout_content; ?>
                <div style="clear: both"></div>
            </div>
        </div>
        <div id="footer">
            
        </div>
    </div>
</div>
</body>
</html>
