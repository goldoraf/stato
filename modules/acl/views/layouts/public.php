<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Login | <?= AclEngine::config('site_title'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="fr" />
<? //= stylesheet_link_tag(array('public')); ?>
</head>
<body>
<div id="container">
    <div id="wrapper">
        <div id="main">
            <div id="header">
                <h1><?= AclEngine::config('site_title'); ?></h1>
            </div>
            
            <div id="content">
                <?= $this->layout_content; ?>
            </div>
        </div>
        <div id="footer">
            
        </div>
    </div>
</div>
</body>
</html>
