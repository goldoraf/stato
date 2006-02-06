<?php

set_include_path(dirname(__FILE__).'/..'.PATH_SEPARATOR.get_include_path());

include_once("cli/cli.php");

try
{
    $controller = new ConsoleController();
    //$controller->run();
}
catch (ConsoleException $e)
{
    echo "ERROR : ".$e->getMessage()."\n";
}

?>
