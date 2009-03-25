<?php

return array(
    new Stato_Table('users', array(
        new Stato_Column('id', Stato_Column::INTEGER, array('primary_key' => true)),
        new Stato_Column('fullname', Stato_Column::STRING),
        new Stato_Column('login', Stato_Column::STRING),
        new Stato_Column('password', Stato_Column::STRING),
    ))
);