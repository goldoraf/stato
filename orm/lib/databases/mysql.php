<?php

class Stato_MysqlDialect implements Stato_Dialect
{
    private static $columnTypes = array(
        'bigint'     => Stato_Column::INTEGER,
        //'binary',
        //'bit',
        //'blob',
        'boolean'    => Stato_Column::BOOLEAN,
        'char'       => Stato_Column::STRING,
        'date'       => Stato_Column::DATE,
        'datetime'   => Stato_Column::DATETIME,
        'decimal'    => Stato_Column::FLOAT,
        'double'     => Stato_Column::FLOAT,
        'enum'       => Stato_Column::STRING,
        'fixed'      => Stato_Column::FLOAT,
        'float'      => Stato_Column::FLOAT,
        'int'        => Stato_Column::INTEGER,
        'integer'    => Stato_Column::INTEGER,
        //'longblob',
        'longtext'   => Stato_Column::STRING,
        //'mediumblob',
        'mediumint'  => Stato_Column::INTEGER,
        'mediumtext' => Stato_Column::TEXT,
        //'nchar',
        //'nvarchar',
        'numeric'    => Stato_Column::FLOAT,
        'set'        => Stato_Column::STRING,
        'smallint'   => Stato_Column::INTEGER,
        'text'       => Stato_Column::TEXT,
        //'time',
        'timestamp'  => Stato_Column::TIMESTAMP,
        //'tinyblob',
        'tinyint'    => Stato_Column::INTEGER,
        'tinytext'   => Stato_Column::TEXT,
        //'varbinary',
        'varchar'    => Stato_Column::STRING,
        'year'       => Stato_Column::STRING,
    );
    
    private static $nativeTypes = array(
        Stato_Column::STRING    => array('type' => 'varchar', 'length' => 255),
        Stato_Column::TEXT      => array('type' => 'text'),
        Stato_Column::FLOAT     => array('type' => 'float'),
        Stato_Column::DATETIME  => array('type' => 'datetime'),
        Stato_Column::DATE      => array('type' => 'date'),
        Stato_Column::TIMESTAMP => array('type' => 'datetime'),
        Stato_Column::INTEGER   => array('type' => 'int', 'length' => 11),
        Stato_Column::BOOLEAN   => array('type' => 'tinyint', 'length' => 1)
    );
    
    public function getDsn(array $params)
    {
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
    
    public function getTableNames(PDO $connection)
    {
        return $connection->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function reflectTable(PDO $connection, $tableName)
    {
        $columns = array();
        $rs = $connection->query("SHOW COLUMNS FROM {$tableName}");
        foreach ($rs as $row) {
            $options = array();
            preg_match('/^(?P<type>\w+)(\((?P<length>\d+)\))?$/', $row['Type'], $options);
            $name = $row['Field'];
            $type = $this->reflectColumnType($options['type']);
            $options['nullable'] = ($row['Null'] == 'YES') ? true : false;
            $options['primary_key'] = ($row['Key'] == 'PRI') ? true : false;
            $options['auto_increment'] = (preg_match('/auto_increment/i', $row['Extra'])) ? true : false;
            $options['default'] = (!empty($row['Default'])) ? $row['Default'] : null;
            $columns[] = new Stato_Column($name, $type, $options);
        }
        return new Stato_Table($tableName, $columns);
    }
    
    public function createTable(Stato_Table $table)
    {
        $columns = array();
        foreach ($table->columns as $column) $columns[] = $this->getColumnSpecification($column);
        if ($table->primaryKey) $columns[] = "PRIMARY KEY (`{$table->primaryKey}`)";
        $columns = implode(',', $columns);
        return "CREATE TABLE `{$table->name}` ({$columns})";
    }
    
    public function dropTable($tableName)
    {
        return "DROP TABLE `{$tableName}`";
    }
    
    public function addColumn($tableName, Stato_Column $column)
    {
        return "ALTER TABLE `{$tableName}` ADD ".$this->getColumnSpecification($column);
    }
    
    public function getColumnSpecification(Stato_Column $column)
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
    
    public function getDefaultValue(Stato_Column $column)
    {
        if ($column->default === null && $column->nullable === true) return 'NULL';
        if ($column->type == Stato_Column::BOOLEAN) return ($column->default === true ? '1' : '0');
        return "'{$column->default}'";
    }
    
    private function reflectColumnType($sqlColumn)
    {
        if (!isset(self::$columnTypes[$sqlColumn]))
            throw new Stato_UnknownColumnType($sqlColumn);
        return self::$columnTypes[$sqlColumn];
    }
}