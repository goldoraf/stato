<?php

$map = new SRouteSet();
$map->connect('admin/:controller/:action/:id', array('subdirectory' => 'admin', 'controller' => 'pages'));
$map->connect('api', array('controller' => 'api', 'action' => 'xmlrpc'));
$map->connect('ajax_api/:action/:id', array('controller' => 'ajax_api'));
$map->connect('tef/centres', array('controller' => 'pages', 'action' => 'centres_tef'));
$map->connect('examens/centres', array('controller' => 'pages', 'action' => 'centres_exam'));
$map->login('login', array('controller' => 'login', 'action' => 'index'));
$map->logout('logout', array('controller' => 'login', 'action' => 'logout'));
$map->auth('authenticate', array('controller' => 'login', 'action' => 'authenticate'));
$map->home('', array('controller' => 'pages', 'action' => 'home'));
$map->contact('contact', array('controller' => 'pages', 'action' => 'contact'));
$map->actu('actualites/:day/:month/:year/:permalink', 
           array('controller' => 'pages', 'action' => 'view_post', 
                 'year' => '/\d{4}/', 'day' => '/\d{1,2}/', 'month' => '/\d{1,2}/', 'permalink' => '/[a-z0-9\-]+/'));
$map->rss('rss', array('controller' => 'pages', 'action' => 'rss'));
$map->page('*path', array('controller' => 'pages', 'action' => 'view'));

return $map;

?>
