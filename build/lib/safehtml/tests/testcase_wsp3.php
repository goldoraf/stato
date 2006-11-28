<?php
/*
This file is for tests - which characters treats by clients as whitespaces inside value of attribute.

IE: 00 09 0A 0D
MZ: none
O7: 09 0A 0D

*/

?>
&lt;a href="test"<?php echo "\x0B"; ?>javascript:alert(1)"&gt;12&lt;/a&gt;
<br/>
<a href="j<?php echo "\x00"; ?>avascript:alert(1)">12</a>0<br/> 
<a href="j<?php echo "\x01"; ?>avascript:alert(1)">12</a>1<br/> 
<a href="j<?php echo "\x02"; ?>avascript:alert(1)">12</a>2<br/> 
<a href="j<?php echo "\x03"; ?>avascript:alert(1)">12</a>3<br/> 
<a href="j<?php echo "\x04"; ?>avascript:alert(1)">12</a>4<br/> 
<a href="j<?php echo "\x05"; ?>avascript:alert(1)">12</a>5<br/> 
<a href="j<?php echo "\x06"; ?>avascript:alert(1)">12</a>6<br/> 
<a href="j<?php echo "\x07"; ?>avascript:alert(1)">12</a>7<br/> 
<a href="j<?php echo "\x08"; ?>avascript:alert(1)">12</a>8<br/> 
<a href="j<?php echo "\x09"; ?>avascript:alert(1)">12</a>9<br/> 
<a href="j<?php echo "\x0A"; ?>avascript:alert(1)">12</a>a<br/> 
<a href="j<?php echo "\x0B"; ?>avascript:alert(1)">12</a>b<br/> 
<a href="j<?php echo "\x0C"; ?>avascript:alert(1)">12</a>c<br/> 
<a href="j<?php echo "\x0D"; ?>avascript:alert(1)">12</a>d<br/> 
<a href="j<?php echo "\x0E"; ?>avascript:alert(1)">12</a>e<br/> 
<a href="j<?php echo "\x0F"; ?>avascript:alert(1)">12</a>f<br/> 
<a href="j<?php echo "\x10"; ?>avascript:alert(1)">12</a>0<br/> 
<a href="j<?php echo "\x11"; ?>avascript:alert(1)">12</a>1<br/> 
<a href="j<?php echo "\x12"; ?>avascript:alert(1)">12</a>2<br/> 
<a href="j<?php echo "\x13"; ?>avascript:alert(1)">12</a>3<br/> 
<a href="j<?php echo "\x14"; ?>avascript:alert(1)">12</a>4<br/> 
<a href="j<?php echo "\x15"; ?>avascript:alert(1)">12</a>5<br/> 
<a href="j<?php echo "\x16"; ?>avascript:alert(1)">12</a>6<br/> 
<a href="j<?php echo "\x17"; ?>avascript:alert(1)">12</a>7<br/> 
<a href="j<?php echo "\x18"; ?>avascript:alert(1)">12</a>8<br/> 
<a href="j<?php echo "\x19"; ?>avascript:alert(1)">12</a>9<br/> 
<a href="j<?php echo "\x1A"; ?>avascript:alert(1)">12</a>a<br/> 
<a href="j<?php echo "\x1B"; ?>avascript:alert(1)">12</a>b<br/> 
<a href="j<?php echo "\x1C"; ?>avascript:alert(1)">12</a>c<br/> 
<a href="j<?php echo "\x1D"; ?>avascript:alert(1)">12</a>d<br/> 
<a href="j<?php echo "\x1E"; ?>avascript:alert(1)">12</a>e<br/> 
<a href="j<?php echo "\x1F"; ?>avascript:alert(1)">12</a>f<br/> 
<a href="j<?php echo "\x20"; ?>avascript:alert(1)">12</a>0<br/> 
