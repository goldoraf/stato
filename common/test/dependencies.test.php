<?php

define('APP_DIR', '/var/www/my_app/app');

class DependenciesTest extends UnitTestCase
{
    public function test_basic()
    {
        $this->assertEqual(APP_DIR."/models/test.php", SDependencies::dependency_file_path('models', 'test'));
        $this->assertEqual(APP_DIR."/models/admin/test.php", SDependencies::dependency_file_path('models', 'admin/test'));
    }
}

?>
