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
        'bigserial'         => Column::INTEGER, //alias de 'bigint'
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
        $columns = array();
        $rs = $connection->execute("SELECT c.column_name, c.data_type, c.character_maximum_length, c.is_nullable, c.column_default, t.constraint_type"
                                  ." FROM information_schema.columns c"
                                  ." LEFT JOIN information_schema.key_column_usage k ON c.table_catalog = k.table_catalog"
                                  ."           AND c.table_name = k.table_name AND c.column_name = k.column_name"
                                  ." LEFT JOIN information_schema.table_constraints t ON t.constraint_name = k.constraint_name"
                                  ."           AND t.constraint_type = 'PRIMARY KEY'"
                                  ." WHERE c.table_name = '{$tableName}';");
        foreach ($rs as $row) {
            $options = array();
            $name = $row['column_name'];
            $type = $this->reflectColumnType($row['data_type']);
            if ($type===Column::STRING) {
                $options['length'] = $row['character_maximum_length'];
            }
            $options['nullable'] = ($row['is_nullable'] == 'YES') ? true : false;
            if (substr($row['column_default'],0,7)=='nextval') {
                $options['auto_increment'] = true;
                $options['default'] = null;
            } else {
                $options['default'] = (!empty($row['Default'])) ? $row['Default'] : null;
            }
            $options['primary_key'] = ($row['constraint_type'] == 'PRIMARY KEY') ? true : false;
            $columns[] = new Column($name, $type, $options);
        }
        return new Table($tableName, $columns);
    }

    public function createTable(Table $table)
    {
        $columns = array();
        $name = $table->getName();
        foreach ($table->getColumns() as $column) $columns[] = $this->getColumnSpecification($column);
        if ($pk = $table->getPrimaryKey()) $columns[] = "PRIMARY KEY (\"{$pk}\")";
        $columns = implode(',', $columns);
        return "CREATE TABLE \"{$name}\" ({$columns})";
    }

    public function truncateTable($tableName)
    {
        return "TRUNCATE TABLE \"{$tableName}\"";
    }

    public function dropTable($tableName)
    {
        return "DROP TABLE \"{$tableName}\"";
    }

    public function addColumn($tableName, Column $column)
    {
        return "ALTER TABLE \"{$tableName}\" ADD COLUMN ".$this->getColumnSpecification($column);
    }

    public function getColumnSpecification(Column $column)
    {
        $nativeType = self::$nativeTypes[$column->type];
        $type = $nativeType['type'];
        if ($column->type===Column::STRING) {
            $length = (isset($nativeType['length'])) ? $nativeType['length'] : null;
            $length = ($column->length !== null) ? $column->length : $length;
        }
        $length = (isset($length) && ($length !== null)) ? "({$length})" : '';
        $default = ($column->default !== false) ? ' DEFAULT '.$this->getDefaultValue($column) : '';
        $nullable = ($column->nullable === false) ? ' NOT NULL' : '';
        if (($column->autoIncrement === true) && ($column->type === Column::INTEGER)) {
            return "\"{$column->name}\" bigserial{$default}{$nullable}";
        }
        return "\"{$column->name}\" {$type}{$length}{$default}{$nullable}";
    }

    public function getDefaultValue(Column $column)
    {
        if ($column->default === null && $column->nullable === true) return 'NULL';
        if ($column->type == Column::BOOLEAN) return ($column->default === true ? '1' : '0');
        if (($column->type == Column::INTEGER) || ($column->type == Column::FLOAT)) return $column->default;
        return "'{$column->default}'";
    }

    private function reflectColumnType($sqlColumn)
    {
        if (!isset(self::$columnTypes[$sqlColumn]))
            throw new UnknownColumnType($sqlColumn);
        return self::$columnTypes[$sqlColumn];
    }
}
