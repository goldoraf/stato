<?php

namespace Stato\Orm;

class Session
{
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}