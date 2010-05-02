<?php

namespace Stato\Dbal\Dialect;

use Stato\Dbal\Table;
use Stato\Dbal\Column;
use Stato\Dbal\Connection;

use \PDO;

class Pgsql implements IDialect
{
    private static $columnTypes = array(
        'bigint'            => Column::INTEGER,
        'int8'              => Column::INTEGER, //alias de 'bigint'
        'bigserial8'        => Column::INTEGER, //alias de 'bigint'
        'serial8'           => Column::INTEGER, //alias de 'bigint'
        //'bit',
        //'bit varying',
        //'varbit',
        'boolean'           => Column::BOOLEAN,
        'bool'              => Column::BOOLEAN, //alias de 'boolean'
        //'box',
        //'bytea',
        'character varying' => Column::STRING,
        'varchar'           => Column::STRING,  //alias de 'character varying'
        'character'         => Column::STRING,
        'char'              => Column::STRING,  //alias de 'character'
        //'cidr',
        //'circle',
        'date'              => Column::DATE,
        'double precision'  => Column::FLOAT,
        'float8'            => Column::FLOAT,   //alias de 'double precision'
        //'inet',
        'integer'           => Column::INTEGER,
        'int'               => Column::INTEGER, //alias de 'integer'
        'int4'              => Column::INTEGER, //alias de 'integer'
        //'interval',
        //'line',
        //'lseg',
        //'macaddr',
        //'money',
        'numeric'           => Column::FLOAT,
        'decimal'           => Column::FLOAT,   //alias de 'numeric'
        //'path',
        //'point',
        //'polygon',
        'real'              => Column::FLOAT,
        'float4'            => Column::FLOAT,   //alias de 'real'
        'smallint'          => Column::INTEGER,
        'int2'              => Column::INTEGER, //alias de 'smallint'
        'serial'            => Column::INTEGER,
        'serial4'           => Column::INTEGER, //alias de 'serial'
        'text'              => Column::TEXT,
        //'time without time zone',
        //'time',
        //'time with time zone',
        //'timetz',
        'timestamp without time zone' => Column::TIMESTAMP,
        'timestamp'                   => Column::TIMESTAMP, //alias de 'timestamp without time zone'
        'timestamp with time zone'    => Column::TIMESTAMP,
        'timestamptz'                 => Column::TIMESTAMP, //alias de 'timestamp with time zone'
        //'tsquery',
        //'tsvector',
        //'txid_snapshot',
        //'uuid',
        //'xml',
    );

    private static $nativeTypes = array(
        Column::STRING    => array('type' => 'character varying', 'length' => 255),
        Column::TEXT      => array('type' => 'text'),
        Column::FLOAT     => array('type' => 'real'),
        Column::DATETIME  => array('type' => 'timestamp without time zone'),
        Column::DATE      => array('type' => 'date'),
        Column::TIMESTAMP => array('type' => 'timestamp without time zone'),
        Column::INTEGER   => array('type' => 'bigint'),
        Column::BOOLEAN   => array('type' => 'boolean')
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
