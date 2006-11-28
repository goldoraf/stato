<?php
/*

    Example for Safehtml

*/

 require_once('../classes/safehtml.php');

 $doc = '
<a href="test"'."\x08".'onmouseover="alert(1)">12</a>8 <br /> 
<a href="test"'."\x09".'onmouseover="alert(1)">12</a>9 <br /> 
<a href="test"'."\x0A".'onmouseover="alert(1)">12</a>a <br /> 
<a href="test"'."\x0B".'onmouseover="alert(1)">12</a>b <br /> 
<a href="test"'."\x0C".'onmouseover="alert(1)">12</a>c <br /> 
<a href="test"'."\x0D".'onmouseover="alert(1)">12</a>d <br /> 
<a href="test"'."\x20".'onmouseover="alert(1)">12</a>20 <br /> 
<a href="test"'."\x00".'onmouseover="alert(1)">12</a>0 <br /> 
';

 // Instantiate the handler
 $safehtml =& new safehtml();

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
