<?php

namespace Stato\Dbal\Dialect;

use Stato\Dbal\Table;
use Stato\Dbal\Column;
use Stato\Dbal\Connection;

use \PDO;

class Pgsql implements IDialect
{
    private static $columnTypes = array(
    );

    private static $nativeTypes = array(
    );

    private $name = 'pgsql';

    public function getDsn(array $params)
    {
    }
    
    public function getDriverOptions()
    {
    }

    public function getTableNames(Connection $connection)
    {
    }

    public function createDatabase($dbName)
    {
    }
    
    public function dropDatabase($dbName)
    {
    }

    public function reflectTable(Connection $connection, $tableName)
    {
    }

    public function createTable(Table $table)
    {
    }

    public function truncateTable($tableName)
    {
    }

    public function dropTable($tableName)
    {
    }

    public function addColumn($tableName, Column $column)
    {
    }

    public function getColumnSpecification(Column $column)
    {
    }

    public function getDefaultValue(Column $column)
    {
    }

    private function reflectColumnType($sqlColumn)
    {
    }
}
