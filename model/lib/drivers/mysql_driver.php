<?php

class SMySqlDriver extends SAbstractDriver
{
    public $nativeDbTypes = array
    (
        'primary_key'   => 'int(11) DEFAULT NULL auto_increment PRIMARY KEY',
        'string'        => array('name' => 'varchar', 'limit' => 255),
        'text'          => array('name' => 'text'),
        'float'         => array('name' => 'float'),
        'datetime'      => array('name' => 'datetime'),
        'date'          => array('name' => 'date'),
        'integer'       => array('name' => 'int', 'limit' => 11),
        'boolean'       => array('name' => 'tinyint', 'limit' => 1)
    );
    
    protected $simplifiedTypes = array
    (
        '/tinyint|smallint|mediumint|int|bigint/i'  => 'integer',
        '/tinytext|text|mediumtext|longtext/i'      => 'text',
        '/float|double|decimal/i'                   => 'float',
        '/varchar|char/i'                           => 'string',
        '/datetime|timestamp/i'                     => 'datetime',
        '/date/i'                                   => 'date',
        '/enum|set/i'                               => 'string'
    );

    public function connect()
    {
        $this->conn = @mysql_connect($this->config['host'],
                                     $this->config['user'],
                                     $this->config['pass']);
        
        mysql_select_db($this->config['dbname']);
        
        mysql_query("SET NAMES 'utf8'");
    }
    
    public function disconnect()
    {
        mysql_close($this->conn);
        $this->conn = null;
    }
    
    public function getError()
    {
        return mysql_errno($this->conn) . ": " . mysql_error($this->conn). "\n";
    }

    public function execute($strsql)
    {
        $result = @mysql_query($strsql,$this->conn);
        if (is_resource($result)) return new SRecordset($result, get_class($this));
        
        if (!$result)
            throw new SInvalidStatementException('MySQL Error : '.$this->getError().' ; SQL used : '.$strsql);
            
        return true;
    }
    
    public function columns($table)
    {
        $rs = $this->execute("SHOW COLUMNS FROM ".$table);
        if ($rs)
        {
            $fields = array();
            while($row = $rs->fetch())
            {
                $fields[$row['Field']] = new SAttribute($row['Field'], 
                                                        $this->simplifiedType($row['Type']), 
                                                        $row['Default']);
            }
            return $fields;
        }
        return false;
    }
    
    public function simplifiedType($sqlType)
    {
        if ($sqlType == 'tinyint(1)') return 'boolean';
        return parent::simplifiedType($sqlType);
    }
    
    public function lastInsertId()
    {
        return mysql_insert_id($this->conn);
    }
    
    public function affectedRows()
    {
        return mysql_affected_rows($this->conn);
    }
    
    public static function rowCount($resource)
    {
        return @mysql_num_rows($resource);
    }
    
    public static function fetch($resource, $associative = true)
    {
        if ($associative) return @mysql_fetch_assoc($resource);
        else return @mysql_fetch_row($resource);
    }
    
    public function getLastUpdate($table)
    {
        $rs = $this->execute("SHOW TABLE STATUS LIKE '".$table."'");
        if (!$this->isError($rs))
        {
            $status = $rs->fetch();
            return $status['Update_time'];
        }
        return false;
    }
    
    public function limit($count, $offset=0)
    {
        if ($count > 0)
        {
            $sql = " LIMIT $count";
            if ($offset > 0) $sql .= " OFFSET $offset";
        }
        return $sql;
    }
    
    public function escapeStr($str)
    {
        // throw exception if magic_quotes ?
        return mysql_real_escape_string($str, $this->conn);
    }
    
    public function quoteColumnName($name)
    {
        return "`$name`";
    }
    
    /**
     * SCHEMA STATEMENTS =======================================================
     **/
    public function tables()
    {
        $tables = array();
        $rs = $this->execute('SHOW TABLES');
        while ($row = $rs->fetch(false)) $tables[] = $row[0];
        return $tables;
    } 
    
    public function createTable($name, $tableDef, $options = array())
    {
        $sql = "CREATE TABLE $name (";
        $sql.= $tableDef->toSql();
        $sql.= ")";
        
        $this->execute($sql);
    }
    
    public function renameTable($oldName, $newName)
    {
        $this->execute("RENAME TABLE $oldName TO $newName");
    }
    
    public function dropTable($name)
    {
        $this->execute("DROP TABLE $name");
    }
    
    public function addColumn($tableName, $columnName, $type, $options = array())
    {
        $sql = "ALTER TABLE $tableName ADD $columnName ".self::typeToSql($type, $options['limit']);
        $sql = self::addColumnOptions($sql, $type, $options);
        $this->execute($sql);
    }
    
    public function removeColumn($tableName, $columnName)
    {
        $this->execute("ALTER TABLE {$tableName} DROP {$columnName}");
    }
    
    public function changeColumn($tableName, $columnName, $type, $options = array())
    {
        if (!isset($options['default']))
        {
            $column = $this->selectOne("SHOW COLUMNS FROM $tableName LIKE '$columnName'");
            $options['default'] = $column['Default'];
        }
        $sql = "ALTER TABLE $tableName CHANGE $columnName $columnName "
        .self::typeToSql($type, $options['limit']);
        $sql = self::addColumnOptions($sql, $type, $options);
        $this->execute($sql);
    }
    
    public function renameColumn($tableName, $columnName, $newName)
    {
        $column = $this->selectOne("SHOW COLUMNS FROM $tableName LIKE '$columnName'");
        $currentType = $column['Type'];
        $this->execute("ALTER TABLE $tableName CHANGE $columnName $newName $currentType");
    }
    
    public function addIndex($tableName, $columnName, $options = array())
    {
        if (isset($options['name'])) $indexName = $options['name'];
        else
        {
            if (!is_array($columnName)) $columnName = array($columnName);
            $indexName = "{$tableName}_".$columnName[0]."_index";
        }
        if (isset($options['unique'])) $indexType = 'UNIQUE';
        else $indexType = '';
        
        $this->execute("CREATE {$indexType} INDEX {$indexName} ON {$tableName} (".implode(', ', $columnName).")");
    }
    
    public function removeIndex($tableName, $options = array())
    {
        if (!is_array($options)) $options = array('column' => $options);
        $this->execute("DROP INDEX ".self::indexName($tableName, $options)." ON {$tableName}");
    }
    
    public function indexName($tableName, $options = array())
    {
        if (isset($options['column']))
            return "{$tableName}_".$options['column']."_index";
        elseif (isset($options['name']))
            return $options['name'];
        else
            throw new SException('You must specify the index name');
    }
    
    public function typeToSql($type, $limit = Null)
    {
        $native = $this->nativeDbTypes[$type];
        if (!is_array($native)) $sql = $native;
        else
        {
            if ($limit === Null && isset($native['limit'])) $limit = $native['limit'];
            $sql = $native['name'];
            if ($limit !== Null) $sql.= "($limit)";
        }
        return $sql;
    }
    
    public function addColumnOptions($sql, $type, $options)
    {
        if ($options['default'] !== Null)
            $sql.= ' DEFAULT '.$this->quote($options['default'], $type);
        if ($options['null'] === False) $sql.= ' NOT NULL';
        return $sql;
    }
    
    public function initializeSchemaInformation()
    {
        try
        {
            $this->execute('CREATE TABLE '.SMigrator::$schemaInfoTableName.' (version '
            .$this->typeToSql('integer').')');
            $this->execute('INSERT INTO '.SMigrator::$schemaInfoTableName.' (version) VALUES (0)');
        }
        catch (Exception $e) {}
    }
}

?>
