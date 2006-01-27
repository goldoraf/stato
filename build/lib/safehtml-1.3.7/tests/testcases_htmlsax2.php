<?php
/*

    Example for Safehtml

*/

 require_once('../classes/safehtml.php');

 $doc = '
<a href="'."\x1B".'javascript:alert(1)">12</a>b<br/> 
<a href="'."\x1C".'javascript:alert(1)">12</a>c<br/> 
<a href="'."\x1D".'javascript:alert(1)">12</a>d<br/> 
<a href="'."\x1E".'javascript:alert(1)">12</a>e<br/> 
<a href="j'."\x00".'avascript:alert(1)">12</a>e<br/> 
';

 // Instantiate the handler
 $safehtml =& new safehtml();
 $safehtml->protocolFiltering = "black";

 $result = $safehtml->parse($doc);

 echo ('<b>Source code before filtration:</b><br/>');
 echo ( htmlspecialchars($doc) );

 echo ('<p><b>Code before filtration as is (HTML):</b><br/>');
 echo ( $doc );

 echo ('<p><b>Source code after filtration:</b><br/>');
 echo ( htmlspecialchars($result) );

 echo ('<p><b>Code after filtration as is (HTML):</b><br/>');
 echo ( $result );

?>
