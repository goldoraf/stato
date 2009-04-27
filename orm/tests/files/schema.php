<?php

return array(
    new Stato_Table('users', array(
        new Stato_Column('id', Stato_Column::INTEGER, array('primary_key' => true)),
        new Stato_Column('fullname', Stato_Column::STRING),
        new Stato_Column('login', Stato_Column::STRING),
        new Stato_Column('password', Stato_Column::STRING),
    )),
    new Stato_Table('posts', array(
        new Stato_Column('id', Stato_Column::INTEGER, array('primary_key' => true)),
        new Stato_Column('title', Stato_Column::STRING),
        new Stato_Column('author', Stato_Column::STRING),
        new Stato_Column('text', Stato_Column::STRING),
        new Stato_Column('published', Stato_Column::BOOLEAN),
        new Stato_Column('created_on', Stato_Column::DATETIME),
        new Stato_Column('updated_on', Stato_Column::DATETIME),
    ))
);