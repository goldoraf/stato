<?php

$config = array
(
    'development' => array
    (
        'adapter' => 'MySql',
        'host'    => 'localhost',
        'user'    => 'root',
        'pass'    => 'gemini65',
        'dbname'  => 'fda'
    ),
    'production' => array
    (
        'adapter' => 'MySql',
        'host'    => 'localhost',
        'user'    => 'sql-fda',
        'pass'    => 'CestPasFaux',
        'dbname'  => 'fda'
    ),
    'test' => array
    (
        'driver'  => 'MySql',
        'host'    => 'localhost',
        'user'    => '',
        'pass'    => '',
        'dbname'  => ''
    )
);

return $config;

?>
