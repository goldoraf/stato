<?php

class SMySqlAdapter extends SAbstractAdapter
{
    public $native_db_types = array
    (
        SColumn::PK       => 'int(11) DEFAULT NULL auto_increment',
        SColumn::STRING   => array('name' => 'varchar', 'limit' => 255),
        SColumn::TEXT     => array('name' => 'text'),
        SColumn::FLOAT    => array('name' => 'float'),
        SColumn::DATETIME => array('name' => 'datetime'),
        SColumn::DATE     => array('name' => 'date'),
        SColumn::INTEGER  => array('name' => 'int', 'limit' => 11),
        SColumn::BOOLEAN  => array('name' => 'tinyint', 'limit' => 1)
    );
    
    public function connect()
    {
        if (!isset($this->config['library']) || $this->config['library'] == 'pdo')
            $this->conn = new SPdoMysqlLibraryWrapper();
        elseif ($this->config['library'] == 'mysql')
            $this->conn = new SMysqlLibraryWrapper();
        else
            throw new Exception($this->config['library'].' library unknown');
            
        $this->conn->connect($this->config['host'], 
                             $this->config['user'],
                             $this->config['pass'],
                             $this->config['dbname']);
    }
    
    public function disconnect()
    {
        $this->conn->disconnect();
    }

    public function execute($sql, $name = null)
    {
        $start = microtime(true);
        
        $result = $this->conn->query($sql);
        
        $time = microtime(true) - $start;
        $this->log($sql, $time, $name);
        $this->runtime += $time;
        
        return $result;
    }
    
    public function query_columns($table)
    {
        $rs = $this->execute("SHOW COLUMNS FROM ".$table);
        $fields = array();
        while($row = $this->fetch($rs))
            $fields[$row['Field']] = new SColumn($row['Field'], 
                                                 $this->simplified_type($row['Type']), 
                                                 $row['Default']);
        return $fields;
    }
    
    public function simplified_type($sql_type)
    {
        if ($sql_type == 'tinyint(1)') 
            return SColumn::BOOLEAN;
        elseif (preg_match('/enum|set/i', $sql_type))
            return SColumn::STRING;
        return parent::simplified_type($sql_type);
    }
    
    public function get_last_update($table)
    {
        $rs = $this->execute("SHOW TABLE STATUS LIKE '".$table."'");
        $status = $this->fetch($rs);
        return $status['Update_time'];
    }
    
    public function limit($count, $offset=0)
    {
        if ($count > 0)
        {
            $sql = "LIMIT $count";
            if ($offset > 0) $sql .= " OFFSET $offset";
        }
        return $sql;
    }
    
    public function quote_column_name($name)
    {
        return "`$name`";
    }
    
    public function supports_transactions()
    {
        return $this->conn->supports_transactions();
    }
    
    public function begin_transaction()
    {
        return $this->conn->begin_transaction();
    }
    
    public function commit()
    {
        return $this->conn->commit();
    }
    
    public function rollback()
    {
        return $this->conn->rollback();
    }
    
    /**
     * SCHEMA STATEMENTS =======================================================
     **/
    public function tables()
    {
        $tables = array();
        $rs = $this->execute('SHOW TABLES');
        while ($row = $this->fetch($rs, false)) $tables[] = $row[0];
        return $tables;
    }
    
    public function table_exists($name)
    {
        return in_array($name, $this->tables());
    }
    
    public function create_table($name, $table_def, $options = null)
    {
        $sql = "CREATE TABLE $name (";
        $sql.= $table_def->to_sql();
        $sql.= ")";
        if ($options !== null) $sql.= ' '.$options;
        
        $this->execute($sql);
    }
    
    public function rename_table($old_name, $new_name)
    {
        $this->execute("RENAME TABLE $old_name TO $new_name");
    }
    
    public function drop_table($name)
    {
        $this->execute("DROP TABLE $name");
    }
    
    public function add_column($table_name, $column_name, $type, $options = array())
    {
        $sql = "ALTER TABLE $table_name ADD $column_name ".self::type_to_sql($type, $options);
        $sql = self::add_column_options($sql, $type, $options);
        $this->execute($sql);
    }
    
    public function remove_column($table_name, $column_name)
    {
        $this->execute("ALTER TABLE {$table_name} DROP {$column_name}");
    }
    
    public function change_column($table_name, $column_name, $type, $options = array())
    {
        if (!isset($options['default']))
        {
            $column = $this->select_one("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
            $options['default'] = $column['Default'];
        }
        $sql = "ALTER TABLE $table_name CHANGE $column_name $column_name "
        .self::type_to_sql($type, $options);
        $sql = self::add_column_options($sql, $type, $options);
        $this->execute($sql);
    }
    
    public function rename_column($table_name, $column_name, $new_name)
    {
        $column = $this->select_one("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
        $current_type = $column['Type'];
        $this->execute("ALTER TABLE $table_name CHANGE $column_name $new_name $current_type");
    }
    
    public function add_index($table_name, $column_name, $options = array())
    {
        if (isset($options['name'])) $index_name = $options['name'];
        else
        {
            if (!is_array($column_name)) $column_name = array($column_name);
            $index_name = "{$table_name}_".$column_name[0]."_index";
        }
        if (isset($options['unique'])) $index_type = 'UNIQUE';
        else $index_type = '';
        
        $this->execute("CREATE {$index_type} INDEX {$index_name} ON {$table_name} (".implode(', ', $column_name).")");
    }
    
    public function remove_index($table_name, $options = array())
    {
        if (!is_array($options)) $options = array('column' => $options);
        $this->execute("DROP INDEX ".self::index_name($table_name, $options)." ON {$table_name}");
    }
    
    public function index_name($table_name, $options = array())
    {
        if (isset($options['column']))
            return "{$table_name}_".$options['column']."_index";
        elseif (isset($options['name']))
            return $options['name'];
        else
            throw new Exception('You must specify the index name');
    }
    
    public function type_to_sql($type, $options = array())
    {
        $native = $this->native_db_types[$type];
        if (!is_array($native)) $sql = $native;
        else
        {
            if (!isset($options['limit']) && isset($native['limit'])) $options['limit'] = $native['limit'];
            $sql = $native['name'];
            if (isset($options['limit'])) $sql.= '('.$options['limit'].')';
        }
        return $sql;
    }
    
    public function add_column_options($sql, $type, $options)
    {
        if (isset($options['default']) && $options['default'] !== null)
            $sql.= ' DEFAULT '.$this->quote($options['default'], $type);
        if (isset($options['null']) && $options['null'] === false) $sql.= ' NOT NULL';
        if (isset($options['after'])) $sql.= ' AFTER '.$options['after'];
        return $sql;
    }
    
    public function initialize_schema_information()
    {
        try
        {
            $this->execute('CREATE TABLE '.SMigrator::$schema_info_table_name.' (version '
            .$this->type_to_sql('integer').')');
            $this->execute('INSERT INTO '.SMigrator::$schema_info_table_name.' (version) VALUES (0)');
        }
        catch (Exception $e) {}
    }
}

?>
