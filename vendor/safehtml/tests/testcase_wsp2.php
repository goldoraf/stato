<?php
/*
This file is for tests - which characters treats by clients as whitespaces before value of attribute.

IE: all
MZ: 08-0A 0D 20
O7: 09-0D 20

*/

?>
&lt;a href="test"<?php echo "\x0B"; ?>javascript:alert(1)"&gt;12&lt;/a&gt;
<br/>
<a href="<?php echo "\x00"; ?>javascript:alert(1)">12</a>0<br/> 
<a href="<?php echo "\x01"; ?>javascript:alert(1)">12</a>1<br/> 
<a href="<?php echo "\x02"; ?>javascript:alert(1)">12</a>2<br/> 
<a href="<?php echo "\x03"; ?>javascript:alert(1)">12</a>3<br/> 
<a href="<?php echo "\x04"; ?>javascript:alert(1)">12</a>4<br/> 
<a href="<?php echo "\x05"; ?>javascript:alert(1)">12</a>5<br/> 
<a href="<?php echo "\x06"; ?>javascript:alert(1)">12</a>6<br/> 
<a href="<?php echo "\x07"; ?>javascript:alert(1)">12</a>7<br/> 
<a href="<?php echo "\x08"; ?>javascript:alert(1)">12</a>8<br/> 
<a href="<?php echo "\x09"; ?>javascript:alert(1)">12</a>9<br/> 
<a href="<?php echo "\x0A"; ?>javascript:alert(1)">12</a>a<br/> 
<a href="<?php echo "\x0B"; ?>javascript:alert(1)">12</a>b<br/> 
<a href="<?php echo "\x0C"; ?>javascript:alert(1)">12</a>c<br/> 
<a href="<?php echo "\x0D"; ?>javascript:alert(1)">12</a>d<br/> 
<a href="<?php echo "\x0E"; ?>javascript:alert(1)">12</a>e<br/> 
<a href="<?php echo "\x0F"; ?>javascript:alert(1)">12</a>f<br/> 
<a href="<?php echo "\x10"; ?>javascript:alert(1)">12</a>0<br/> 
<a href="<?php echo "\x11"; ?>javascript:alert(1)">12</a>1<br/> 
<a href="<?php echo "\x12"; ?>javascript:alert(1)">12</a>2<br/> 
<a href="<?php echo "\x13"; ?>javascript:alert(1)">12</a>3<br/> 
<a href="<?php echo "\x14"; ?>javascript:alert(1)">12</a>4<br/> 
<a href="<?php echo "\x15"; ?>javascript:alert(1)">12</a>5<br/> 
<a href="<?php echo "\x16"; ?>javascript:alert(1)">12</a>6<br/> 
<a href="<?php echo "\x17"; ?>javascript:alert(1)">12</a>7<br/> 
<a href="<?php echo "\x18"; ?>javascript:alert(1)">12</a>8<br/> 
<a href="<?php echo "\x19"; ?>javascript:alert(1)">12</a>9<br/> 
<a href="<?php echo "\x1A"; ?>javascript:alert(1)">12</a>a<br/> 
<a href="<?php echo "\x1B"; ?>javascript:alert(1)">12</a>b<br/> 
<a href="<?php echo "\x1C"; ?>javascript:alert(1)">12</a>c<br/> 
<a href="<?php echo "\x1D"; ?>javascript:alert(1)">12</a>d<br/> 
<a href="<?php echo "\x1E"; ?>javascript:alert(1)">12</a>e<br/> 
<a href="<?php echo "\x1F"; ?>javascript:alert(1)">12</a>f<br/> 
<a href="<?php echo "\x20"; ?>javascript:alert(1)">12</a>0<br/> 
