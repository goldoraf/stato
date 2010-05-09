<?php

namespace Stato\Dbal\Dialect;

use Stato\Dbal\Table;
use Stato\Dbal\Column;
use Stato\Dbal\Connection;

use \PDO, \Exception;

class Mysql implements IDialect
{
    private static $columnTypes = array(
        'bigint'     => Column::INTEGER,
        //'binary',
        //'bit',
        //'blob',
        'boolean'    => Column::BOOLEAN,
        'char'       => Column::STRING,
        'date'       => Column::DATE,
        'datetime'   => Column::DATETIME,
        'decimal'    => Column::FLOAT,
        'double'     => Column::FLOAT,
        'enum'       => Column::STRING,
        'fixed'      => Column::FLOAT,
        'float'      => Column::FLOAT,
        'int'        => Column::INTEGER,
        'integer'    => Column::INTEGER,
        //'longblob',
        'longtext'   => Column::TEXT,
        //'mediumblob',
        'mediumint'  => Column::INTEGER,
        'mediumtext' => Column::TEXT,
        //'nchar',
        //'nvarchar',
        'numeric'    => Column::FLOAT,
        'set'        => Column::STRING,
        'smallint'   => Column::INTEGER,
        'text'       => Column::TEXT,
        //'time',
        'timestamp'  => Column::TIMESTAMP,
        //'tinyblob',
        'tinyint'    => Column::INTEGER,
        'tinytext'   => Column::TEXT,
        //'varbinary',
        'varchar'    => Column::STRING,
        'year'       => Column::STRING,
    );
    
    private static $nativeTypes = array(
        Column::STRING    => array('type' => 'varchar', 'length' => 255),
        Column::TEXT      => array('type' => 'text'),
        Column::FLOAT     => array('type' => 'float'),
        Column::DATETIME  => array('type' => 'datetime'),
        Column::DATE      => array('type' => 'date'),
        Column::TIMESTAMP => array('type' => 'datetime'),
        Column::INTEGER   => array('type' => 'int', 'length' => 11),
        //Column::BOOLEAN   => array('type' => 'tinyint', 'length' => 1)
        Column::BOOLEAN   => array('type' => 'boolean')
    );
    
    private $name = 'mysql';
    
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
        return 'mysql:'.implode(';', $parts);
    }
    
    public function getDriverOptions()
    {
        return array(/*PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;'*/);
    }
    
    public function getTableNames(Connection $connection)
    {
        $result = $connection->execute('SHOW TABLES');
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
        $rs = $connection->execute("SHOW COLUMNS FROM {$tableName}");
        foreach ($rs as $row) {
            $options = array();
            $name = $row['Field'];
            if ($row['Type'] == 'tinyint(1)') {
                $type = Column::BOOLEAN; // TODO : remove this hack !!!
            } else {
                preg_match('/^(?P<type>\w+)(\((?P<length>\d+)\))?$/', $row['Type'], $options);
                $type = $this->reflectColumnType($options['type']);
            }
            $options['nullable'] = ($row['Null'] == 'YES') ? true : false;
            $options['primary_key'] = ($row['Key'] == 'PRI') ? true : false;
            $options['auto_increment'] = (preg_match('/auto_increment/i', $row['Extra'])) ? true : false;
            $options['default'] = (!empty($row['Default'])) ? $row['Default'] : null;
            $columns[] = new Column($name, $type, $options);
        }
        return new Table($tableName, $columns);
    }
    
    public function createTable(Table $table)
    {
        $columns = array();
        $name = $table->getName();
        foreach ($table->getColumns() as $column) $columns[] = $this->getColumnSpecification($column);
        if ($pk = $table->getPrimaryKey()) $columns[] = "PRIMARY KEY (`{$pk}`)";
        $columns = implode(',', $columns);
        return "CREATE TABLE `{$name}` ({$columns})";
    }
    
    public function truncateTable($tableName)
    {
        return "TRUNCATE TABLE `{$tableName}`";
    }
    
    public function dropTable($tableName)
    {
        return "DROP TABLE `{$tableName}`";
    }
    
    public function addColumn($tableName, Column $column)
    {
        return "ALTER TABLE `{$tableName}` ADD ".$this->getColumnSpecification($column);
    }
    
    public function getColumnSpecification(Column $column)
    {
        $nativeType = self::$nativeTypes[$column->type];
        $type = $nativeType['type'];
        $length = (isset($nativeType['length'])) ? $nativeType['length'] : null;
        $length = ($column->length !== null) ? $column->length : $length;
        $length = ($length !== null) ? "({$length})" : '';
        $default = ($column->default !== false) ? ' DEFAULT '.$this->getDefaultValue($column) : '';
        $nullable = ($column->nullable === false) ? ' NOT NULL' : '';
        $autoincrement = ($column->autoIncrement === true) ? ' auto_increment' : '';
        return "`{$column->name}` {$type}{$length}{$default}{$nullable}{$autoincrement}";
    }
    
    public function getDefaultValue(Column $column)
    {
        if ($column->default === null && $column->nullable === true) return 'NULL';
        if ($column->type == Column::BOOLEAN) return ($column->default === true ? '1' : '0');
        return "'{$column->default}'";
    }
    
    private function reflectColumnType($sqlColumn)
    {
        if (!isset(self::$columnTypes[$sqlColumn]))
            throw new UnknownColumnType($sqlColumn);
        return self::$columnTypes[$sqlColumn];
    }
}
