<?php
/*
This file is for tests - which characters treats by clients as whitespaces between tags.

Maximal set now is:
00
08-0D
20

Tested sets are:
IE6
00
09-0D
20

MZ
08-0A
0D
20

O7.23
09-0D
20
*/

?>
&lt;a href="test"<?php echo "\x0B"; ?>onmouseover="alert(1)"&gt;12&lt;/a&gt;
<br/>
<a href="test"<?php echo "\x00"; ?>onmouseover="alert(1)">12</a>0<br/> 
<a href="test"<?php echo "\x01"; ?>onmouseover="alert(1)">12</a>1<br/> 
<a href="test"<?php echo "\x02"; ?>onmouseover="alert(1)">12</a>2<br/> 
<a href="test"<?php echo "\x03"; ?>onmouseover="alert(1)">12</a>3<br/> 
<a href="test"<?php echo "\x04"; ?>onmouseover="alert(1)">12</a>4<br/> 
<a href="test"<?php echo "\x05"; ?>onmouseover="alert(1)">12</a>5<br/> 
<a href="test"<?php echo "\x06"; ?>onmouseover="alert(1)">12</a>6<br/> 
<a href="test"<?php echo "\x07"; ?>onmouseover="alert(1)">12</a>7<br/> 
<a href="test"<?php echo "\x08"; ?>onmouseover="alert(1)">12</a>8<br/> 
<a href="test"<?php echo "\x09"; ?>onmouseover="alert(1)">12</a>9<br/> 
<a href="test"<?php echo "\x0A"; ?>onmouseover="alert(1)">12</a>a<br/> 
<a href="test"<?php echo "\x0B"; ?>onmouseover="alert(1)">12</a>b<br/> 
<a href="test"<?php echo "\x0C"; ?>onmouseover="alert(1)">12</a>c<br/> 
<a href="test"<?php echo "\x0D"; ?>onmouseover="alert(1)">12</a>d<br/> 
<a href="test"<?php echo "\x0E"; ?>onmouseover="alert(1)">12</a>e<br/> 
<a href="test"<?php echo "\x0F"; ?>onmouseover="alert(1)">12</a>f<br/> 
<a href="test"<?php echo "\x10"; ?>onmouseover="alert(1)">12</a>0<br/> 
<a href="test"<?php echo "\x11"; ?>onmouseover="alert(1)">12</a>1<br/> 
<a href="test"<?php echo "\x12"; ?>onmouseover="alert(1)">12</a>2<br/> 
<a href="test"<?php echo "\x13"; ?>onmouseover="alert(1)">12</a>3<br/> 
<a href="test"<?php echo "\x14"; ?>onmouseover="alert(1)">12</a>4<br/> 
<a href="test"<?php echo "\x15"; ?>onmouseover="alert(1)">12</a>5<br/> 
<a href="test"<?php echo "\x16"; ?>onmouseover="alert(1)">12</a>6<br/> 
<a href="test"<?php echo "\x17"; ?>onmouseover="alert(1)">12</a>7<br/> 
<a href="test"<?php echo "\x18"; ?>onmouseover="alert(1)">12</a>8<br/> 
<a href="test"<?php echo "\x19"; ?>onmouseover="alert(1)">12</a>9<br/> 
<a href="test"<?php echo "\x1A"; ?>onmouseover="alert(1)">12</a>a<br/> 
<a href="test"<?php echo "\x1B"; ?>onmouseover="alert(1)">12</a>b<br/> 
<a href="test"<?php echo "\x1C"; ?>onmouseover="alert(1)">12</a>c<br/> 
<a href="test"<?php echo "\x1D"; ?>onmouseover="alert(1)">12</a>d<br/> 
<a href="test"<?php echo "\x1E"; ?>onmouseover="alert(1)">12</a>e<br/> 
<a href="test"<?php echo "\x1F"; ?>onmouseover="alert(1)">12</a>f<br/> 
<a href="test"<?php echo "\x20"; ?>onmouseover="alert(1)">12</a>0<br/> 
