<?php

namespace Stato\Orm;

class Session
{
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    public function query($entity)
    {
        return new Query($entity, $this->connection);
    }
}