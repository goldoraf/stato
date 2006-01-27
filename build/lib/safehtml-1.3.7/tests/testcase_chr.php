<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
 <title></title>
 <meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>

<body >
<?php

//This file is for tests - which characters treats by clients as BAD.


for ($i=1; $i<256; $i++)
{
 echo chr($i);
}
echo chr(0);
?>
</body>
</html>