<?php

$config = array
(
    'development' => array
    (
        'adapter' => 'MySql',
        'host'    => 'localhost',
        'user'    => '',
        'pass'    => '',
        'dbname'  => ''
    ),
    'production' => array
    (
        'adapter' => 'MySql',
        'host'    => 'localhost',
        'user'    => '',
        'pass'    => '',
        'dbname'  => ''
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
