<?php

use Stato\Orm\Table;
use Stato\Orm\Column;

return array(
    new Table('users', array(
        new Column('id', Column::INTEGER, array('primary_key' => true)),
        new Column('fullname', Column::STRING),
        new Column('login', Column::STRING),
        new Column('password', Column::STRING),
    )),
    new Table('posts', array(
        new Column('id', Column::INTEGER, array('primary_key' => true)),
        new Column('title', Column::STRING),
        new Column('author', Column::STRING),
        new Column('text', Column::STRING),
        new Column('published', Column::BOOLEAN),
        new Column('created_on', Column::DATETIME),
        new Column('updated_on', Column::DATETIME),
    ))
);