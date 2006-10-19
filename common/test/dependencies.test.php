<?php

define('APP_DIR', '/var/www/my_app/app');

class DependenciesTest extends UnitTestCase
{
    public function testBasic()
    {
        $this->assertEqual(APP_DIR."/models/test.php", SDependencies::dependencyFilePath('models', 'test'));
        $this->assertEqual(APP_DIR."/models/admin/test.php", SDependencies::dependencyFilePath('models', 'admin/test'));
    }
}

?>
