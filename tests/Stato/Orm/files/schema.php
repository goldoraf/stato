<?php

require_once 'Stato/Orm/Schema.php';

use Stato\Orm\Table;
use Stato\Orm\Column;

return array(
    'users' => new Table('users', array(
        new Column('id', Column::INTEGER, array('primary_key' => true)),
        new Column('fullname', Column::STRING),
        new Column('login', Column::STRING),
        new Column('password', Column::STRING),
    )),
    'posts' => new Table('posts', array(
        new Column('id', Column::INTEGER, array('primary_key' => true)),
        new Column('title', Column::STRING),
        new Column('author', Column::STRING),
        new Column('text', Column::STRING),
        new Column('published', Column::BOOLEAN),
        new Column('created_on', Column::DATETIME),
        new Column('updated_on', Column::DATETIME),
    )),
    'employes' => new Table('employes', array(
        new Column('id', Column::INTEGER, array('primary_key' => true)),
        new Column('company_id', Column::INTEGER),
        new Column('firstname', Column::STRING),
        new Column('lastname', Column::STRING),
        new Column('function', Column::STRING),
        new Column('date_of_birth', Column::DATETIME),
    )),
    'companies' => new Table('companies', array(
        new Column('id', Column::INTEGER, array('primary_key' => true)),
        new Column('name', Column::STRING),
    )),
    'products' => new Table('products', array(
        new Column('id', Column::INTEGER, array('primary_key' => true)),
        new Column('name', Column::STRING),
        new Column('price', Column::FLOAT),
        new Column('company_id', Column::INTEGER),
    )),
);