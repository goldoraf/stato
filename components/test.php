<?php

define('STATO_CORE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

require STATO_CORE_PATH.'/common/common.php';
require STATO_CORE_PATH.'/components/console/console.php';
require STATO_CORE_PATH.'/components/test/test.php';

$test = new StatoGroupTest('Components tests');
foreach (new DirectoryIterator(STATO_CORE_PATH.'/components') as $elt)
{
    if ($elt->isDir() && !$elt->isDot() && $elt->getFilename() != '.svn')
    {
        $comp = $elt->getFilename();
        $test_folder = STATO_CORE_PATH."/components/{$comp}/test";
        if (is_dir($test_folder))
        {
            require_once STATO_CORE_PATH."/components/{$comp}/{$comp}.php";
            $test->addTestFolder($test_folder);
        }
    }
}
$test->run(new TextReporter());

?>
