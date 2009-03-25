<?php

class Stato_DbSession
{
    public function __construct(Stato_Connection $connection)
    {
        $this->connection = $connection;
    }
    
    public function query($entity)
    {
        return new Stato_Query($entity, $this->connection);
    }
}