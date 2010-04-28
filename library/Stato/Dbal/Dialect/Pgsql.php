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
        if (isset($params['dsn'])) return $params['dsn'];
        
        $parts = array();
        if (isset($params['unix_socket']))
            $parts[] = "unix_socket={$params['unix_socket']}";
        else {
            if (!isset($params['host'])) throw new Exception('No host provided');
            $parts[] = "host={$params['host']}";
            if (isset($params['port'])) $parts[] = "port={$params['port']}";
        }
        if (!isset($params['dbname'])) throw new Exception('No db name provided');
        $parts[] = "dbname={$params['dbname']}";
        return 'pgsql:'.implode(';', $parts);
    }
    
    public function getDriverOptions()
    {
        return array();
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
