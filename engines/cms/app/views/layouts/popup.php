<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Browser</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?= javascript_include_tag(array('jquery-1.2.1.min.js', 'ext-1.1.1-custom_build.js', 
                                 /*'tiny_mce/tiny_mce_popup', 'tiny_mce/utils/mctabs'*/)); ?>
<link rel="stylesheet" type="text/css" media="screen" href="/js/ext-1.1.1/resources/css/ext-all.css" />
<?= stylesheet_link_tag('browse'); ?>
</head>
<body>
    <?= $this->layout_content; ?>
</body>
</html>
