<?php

SActiveRecord::establish_connection(array(
    'adapter' => 'MySql',
    'host'    => 'localhost',
    'user'    => 'stato',
    'dbname'  => 'stato_orm_tests',
    'library' => 'pdo'
));

?>
