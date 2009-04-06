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
define('STATO_APP_ROOT_PATH', STATO_CORE_PATH.'/webflow/lib/templates/createapp'); // for RescueTest
$_SESSION = array();

set_include_path(dirname(__FILE__).'/..' . PATH_SEPARATOR . get_include_path());

require_once 'common/common.php';
require_once 'cli/cli.php';
require_once 'webflow/webflow.php';
require_once 'orm/orm.php';
require_once 'i18n/i18n.php';

require_once 'test/stato_test_case.php';
require_once 'test/active_test_case.php';
require_once 'test/controller_mocks.php';
require_once 'test/mock_record.php';

/*
 * Prepend the backported Stato libs directories to the include_path
 */
$path = array(
    dirname(__FILE__).'/../i18n/lib',
    dirname(__FILE__).'/../mailer/lib',
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

define('STATO_TESTING_ADAPTER', 'pdo_mysql');
define('STATO_FIXTURES_DIR', STATO_CORE_PATH.'/orm/test/fixtures');
require_once STATO_FIXTURES_DIR.'/models.php';

if (!file_exists(STATO_CORE_PATH.'/orm/test/connections/'.STATO_TESTING_ADAPTER.'.php'))
    throw new Exception(STATO_TESTING_ADAPTER.' adapter not found');

require_once STATO_CORE_PATH.'/orm/test/connections/'.STATO_TESTING_ADAPTER.'.php';

/*
 * Exclude tests dirs from code coverage
 */
$testsDirs = array(
    'test',
    'webflow/test',
    'orm/test',
    'common/test'
);
foreach ($testsDirs as $dir) {
    PHPUnit_Util_Filter::addDirectoryToFilter(
      dirname(__FILE__).'/../'.$dir, '.php'
    );
}
