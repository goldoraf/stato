<?php

SActiveRecord::establish_connection(array(
    'adapter' => 'PDOMySql',
    'host'    => 'localhost',
    'user'    => 'stato',
    'dbname'  => 'stato_model_tests'
));

?>
