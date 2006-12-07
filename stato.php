<?php

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__))));

require_once(ROOT_DIR.'/common/common.php');
require_once(ROOT_DIR.'/cli/cli.php');

SConsoleUtils::register_global_options(array('WWW_ROOT', 'APP_NAME'));

$GLOBALS['SCRIPT_ARGS'] = SConsoleUtils::read_arguments();
array_shift($GLOBALS['SCRIPT_ARGS']);
$action = array_shift($GLOBALS['SCRIPT_ARGS']);

if (!in_array($action, array('generate', 'migrate', 'run_tests')))
    die("$action action does not exists");
    
include(ROOT_DIR.'/build/scripts/'.$action.'.php');


?>
