<?php

$routes = array
(
    'photos/{id}' => array('module'     => 'phototheque',
                           'controller' => 'photos',
                           'action'     => 'index',
                           'validate'   => array('id' => '\d+')),
    'photos/{year}/{month}/{day}' => array('module'     => 'phototheque',
                                           'controller' => 'photos',
                                           'action'     => 'archive',
                                           'validate'   => array('year' => '\d4', 'month' => '\d{1,2}', 'day' => '\d{1,2}'))
);

return $routes;

?>
