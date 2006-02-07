<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>CRUD</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?= js_include_tag(array('prototype.js')); ?>
<style>
    body
    {
        background-color: #fff;
        color: #000;
        font-family: verdana, arial, helvetica, sans-serif;
        font-size:12px;
    }
    
    h1 { font-size: 18px; }
    
    table
    {
        border: 1px solid #ccc;
        border-spacing: 0; 
        margin: 0;
        text-align:center;
        border-collapse: collapse;
    }
    th { padding: 2px 10px; }
    td { padding: 10px; border-top: 1px solid #ccc; }
    
    a { color: #666; }
    a:hover { color: #000; }
    
    label { display: block; }
    
    .form-errors
    {
        width: 400px;
        border: 2px solid red;
        padding: 10px 10px 5px;
        background-color: #f0f0f0;
    }
    .form-errors h2 { margin: 0; font-size: 14px; }
</style>
</head>
<body>
    <p style="color: green;"><?= $this->flash['notice']; ?></p>
    <?= $this->layout_content; ?>
</body>
</html>
