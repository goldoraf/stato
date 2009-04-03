<?php

/*
 * Include PHPUnit dependencies
 */
require_once 'PHPUnit/Framework.php';

ob_start();

error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Europe/Paris');

mb_internal_encoding("UTF-8");

setlocale(LC_TIME, 'en_EN.utf8', 'en_EN', 'en');

define('STATO_CORE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));
define('STATO_APP_ROOT_PATH', STATO_CORE_PATH.'/gemini/lib/templates/createapp'); // for RescueTest
$_SESSION = array();

require_once STATO_CORE_PATH.'/common/common.php';
require_once STATO_CORE_PATH.'/components/console/console.php';
require_once STATO_CORE_PATH.'/gemini/gemini.php';
require_once STATO_CORE_PATH.'/mercury/mercury.php';

require_once STATO_CORE_PATH.'/test/stato_test_case.php';
require_once STATO_CORE_PATH.'/test/active_test_case.php';
require_once STATO_CORE_PATH.'/test/controller_mocks.php';
require_once STATO_CORE_PATH.'/test/mock_record.php';

define('STATO_TESTING_ADAPTER', 'pdo_mysql');
define('STATO_FIXTURES_DIR', STATO_CORE_PATH.'/mercury/test/fixtures');
require_once STATO_FIXTURES_DIR.'/models.php';

if (!file_exists(STATO_CORE_PATH.'/mercury/test/connections/'.STATO_TESTING_ADAPTER.'.php'))
    throw new Exception(STATO_TESTING_ADAPTER.' adapter not found');

require_once STATO_CORE_PATH.'/mercury/test/connections/'.STATO_TESTING_ADAPTER.'.php';

/*
 * Exclude tests dirs from code coverage
 */
/*$testsDirs = array(
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
}*/
