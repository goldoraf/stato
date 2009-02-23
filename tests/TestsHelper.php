<?php

/*
 * Include PHPUnit dependencies
 */
require_once 'PHPUnit/Framework.php';

ob_start();

error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Europe/Paris');

/*
 * Prepend the Stato libs directories to the include_path
 */
$path = array(
    dirname(__FILE__),
    dirname(__FILE__).'/../webflow/lib',
    dirname(__FILE__).'/../orm/lib',
    dirname(__FILE__).'/../mailer/lib',
    dirname(__FILE__).'/../i18n/lib',
    dirname(__FILE__).'/../cli/lib',
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

/*
 * Require testing tools
 */
require_once dirname(__FILE__).'/TestEnv.php';
require_once dirname(__FILE__).'/DatabaseTestCase.php';

/*
 * Exclude tests dirs from code coverage
 */
$testsDirs = array(
    'tests',
    'cli/tests',
    'i18n/tests',
    'mailer/tests',
    'webflow/tests',
    'orm/tests'
);
foreach ($testsDirs as $dir) {
    PHPUnit_Util_Filter::addDirectoryToFilter(
      dirname(__FILE__).'/../'.$dir, '.php'
    );
}
