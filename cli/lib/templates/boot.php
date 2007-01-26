// Uncomment below to force Stato in production mode
// when you don't control web server
// $_SERVER['STATO_ENV'] = 'production';

// Dont't change code below. Configuration is done in conf/environment.php

define('STATO_TIME_START', microtime(true));
define('STATO_CORE_PATH', '<?php echo $project_core_path; ?>');
define('STATO_APP_ROOT_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));
define('STATO_ENV', ((isset($_SERVER['STATO_ENV'])) ? $_SERVER['STATO_ENV'] : 'development'));

require(STATO_CORE_PATH.'/common/lib/initializer.php');

SInitializer::boot();
