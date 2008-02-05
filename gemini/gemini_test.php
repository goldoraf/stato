<?php

require_once(STATO_TESTING_PATH.'/controller_test_case.php');
require_once(STATO_TESTING_PATH.'/controller_mocks.php');
require_once(STATO_TESTING_PATH.'/xml_test_case.php');
require_once(STATO_TESTING_PATH.'/helper_test_case.php');

return array
(
    'filters',
    'routes',
    'url_rewriter',
    'mime_type',
    'request',
    'rescue',
    'helpers/tag_helper',
    'helpers/asset_tag_helper',
    'helpers/javascript_helper',
    'helpers/form_helper',
    'helpers/form_options_helper',
    'helpers/date_helper',
    'helpers/active_record_helper',
    'helpers/number_helper'
);

?>
