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
        $result = $connection->execute('SELECT table_name FROM information_schema.tables WHERE table_schema=\'public\' AND table_type=\'BASE TABLE\';');
        $result->setFetchMode(PDO::FETCH_COLUMN, 0);
        return $result->fetchAll();
    }

    public function createDatabase($dbName)
    {
        return "CREATE DATABASE $dbName";
    }
    
    public function dropDatabase($dbName)
    {
        return "DROP DATABASE IF EXISTS $dbName";
    }

    public function reflectTable(Connection $connection, $tableName)
    {
    }

    public function createTable(Table $table)
    {
    }

    public function truncateTable($tableName)
    {
        return "TRUNCATE TABLE {$tableName}";
    }

    public function dropTable($tableName)
    {
        return "DROP TABLE {$tableName}";
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
        if (!isset(self::$columnTypes[$sqlColumn]))
            throw new UnknownColumnType($sqlColumn);
        return self::$columnTypes[$sqlColumn];
    }
}
