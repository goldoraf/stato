<?php

$project_name = array_shift($SCRIPT_ARGS);

if ($project_name === null)
    die('Please specify a name for your project');
    
require_once(ROOT_DIR.'/model/lib/filesystem/dir.php');

$project_path = $WWW_ROOT.'/'.$project_name;
SDir::mkdir($project_path);
foreach (array('app', 'cache', 'conf', 'core', 'db', 'lib', 'log', 'public', 'scripts') as $dir)
    SDir::mkdir("$project_path/$dir");
//SDir::copy(CORE_DIR, "$project_path/core");
SDir::copy(ROOT_DIR.'/build/public', "$project_path/public");
break;

?>
