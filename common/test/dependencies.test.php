<?php

define('STATO_APP_PATH', '/var/www/my_app/app');

class DependenciesTest extends UnitTestCase
{
    public function test_basic()
    {
        $this->assertEqual(STATO_APP_PATH."/models/test.php", SDependencies::dependency_file_path('models', 'test'));
        $this->assertEqual(STATO_APP_PATH."/models/admin/test.php", SDependencies::dependency_file_path('models', 'admin/test'));
    }
}

?>
