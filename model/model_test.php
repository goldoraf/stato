<?php

define('STATO_FIXTURES_DIR', STATO_CORE_PATH.'/model/test/fixtures');
require_once(STATO_FIXTURES_DIR.'/models.php');
require_once(STATO_TESTING_PATH.'/active_test_case.php');

if (!file_exists(STATO_CORE_PATH.'/model/test/connections/'.STATO_TESTING_ADAPTER.'.php'))
    throw new Exception(STATO_TESTING_ADAPTER.' adapter not found');

require_once(STATO_CORE_PATH.'/model/test/connections/'.STATO_TESTING_ADAPTER.'.php');

return array
(
    'active_record',
    'queryset',
    'eager_loading',
    'associations',
    'callbacks',
    'decorators',
    'list_decorator',
    'tree_decorator',
    'migration'
);

?>
