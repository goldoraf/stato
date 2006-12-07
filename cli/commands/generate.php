<?php

$type = array_shift($SCRIPT_ARGS);
if ($type == null)
    die('What do you want to generate ?');
if (!in_array($type, array('model', 'project')))
    die("I don't know how to generate a $type");
    
include(ROOT_DIR."/build/scripts/generate_$type.php");

?>
