<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

require_once 'i18n.php';
require_once 'backend/abstract.php';
require_once 'backend/xliff.php';

class SXliffBackendTest extends SYamlBackendTest
{
    public function setup()
    {
        SI18n::add_data_path(dirname(__FILE__).'/data/xliff');
        $this->backend = new SXliffBackend();
    }
}