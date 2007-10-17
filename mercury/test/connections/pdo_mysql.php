<?php

SActiveRecord::establish_connection(array(
    'adapter' => 'MySql',
    'host'    => 'localhost',
    'user'    => 'stato',
    'dbname'  => 'stato_model_tests',
    'library' => 'pdo'
));

?>
