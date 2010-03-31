<?php

require_once __DIR__ . '/../TestsHelper.php';

set_include_path(__DIR__ . '/models' . PATH_SEPARATOR . get_include_path());

\Stato\TestEnv::createTestDatabase();

\Stato\Model\Repository::setup('default', \Stato\TestEnv::getDbConfig());
