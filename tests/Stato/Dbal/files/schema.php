<?php

require_once 'Stato/Dbal/Schema.php';

use Stato\Dbal\Table;
use Stato\Dbal\Column;

return array(
    'users' => new Table('users', array(
        new Column('id', Column::INTEGER, array('primary_key' => true, 'auto_increment' => true)),
        new Column('fullname', Column::STRING),
        new Column('login', Column::STRING),
        new Column('password', Column::STRING),
        new Column('country', Column::STRING),
        new Column('activated', Column::BOOLEAN),
        new Column('registration_date', Column::DATETIME),
    )),
    'users_light' => new Table('users_light', array(
        new Column('id', Column::INTEGER, array('primary_key' => true, 'auto_increment' => true)),
        new Column('fullname', Column::STRING),
        new Column('login', Column::STRING),
        new Column('password', Column::STRING)
    )),
    'posts' => new Table('posts', array(
        new Column('id', Column::INTEGER, array('primary_key' => true, 'auto_increment' => true)),
        new Column('title', Column::STRING),
        new Column('author', Column::STRING),
        new Column('text', Column::TEXT),
        new Column('published', Column::BOOLEAN),
        new Column('created_on', Column::DATETIME),
        new Column('updated_on', Column::DATETIME),
    )),
    'employes' => new Table('employes', array(
        new Column('id', Column::INTEGER, array('primary_key' => true, 'auto_increment' => true)),
        new Column('company_id', Column::INTEGER),
        new Column('firstname', Column::STRING),
        new Column('lastname', Column::STRING),
        new Column('function', Column::STRING),
        new Column('date_of_birth', Column::DATETIME),
    )),
    'companies' => new Table('companies', array(
        new Column('id', Column::INTEGER, array('primary_key' => true, 'auto_increment' => true)),
        new Column('name', Column::STRING),
    )),
    'products' => new Table('products', array(
        new Column('id', Column::INTEGER, array('primary_key' => true, 'auto_increment' => true)),
        new Column('name', Column::STRING),
        new Column('price', Column::FLOAT),
        new Column('company_id', Column::INTEGER),
    )),
    'articles' => new Table('articles', array(
        new Column('id', Column::INTEGER, array('nullable' => false, 'primary_key' => true, 'auto_increment' => true)),
        new Column('title', Column::STRING, array('length' => 50)),
        new Column('body', Column::TEXT),
        new Column('published', Column::BOOLEAN),
        new Column('created_on', Column::DATETIME),
        new Column('price', Column::FLOAT),
    )),
    'events' => new Table('events', array(
        new Column('id', Column::INTEGER, array('nullable' => false, 'primary_key' => true, 'auto_increment' => true)),
        new Column('title', Column::STRING, array('length' => 50)),
        
    )),
);
