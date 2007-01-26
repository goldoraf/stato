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
    
    public function get_error()
    {
        return mysql_errno($this->conn) . ": " . mysql_error($this->conn). "\n";
    }

    public function execute($strsql, $name = null)
    {
        $start = microtime(true);
        
        $result = @mysql_query($strsql,$this->conn);
        
        $time = microtime(true) - $start;
        $this->log($strsql, $time, $name);
        $this->runtime += $time;
        
        if (is_resource($result)) return $result;
        
        if (!$result)
            throw new SInvalidStatementException('MySQL Error : '.$this->get_error().' ; SQL used : '.$strsql);
            
        return true;
    }
    
    public function columns($table)
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
    
    public function last_insert_id()
    {
        return mysql_insert_id($this->conn);
    }
    
    public function affected_rows()
    {
        return mysql_affected_rows($this->conn);
    }
    
    public function row_count($resource)
    {
        return @mysql_num_rows($resource);
    }
    
    public function free_result($resource)
    {
        return @mysql_free_result($resource);
    }
    
    public function fetch($resource, $associative = true)
    {
        if ($associative) return @mysql_fetch_assoc($resource);
        else return @mysql_fetch_row($resource);
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
    
    public function escape_str($str)
    {
        // throw exception if magic_quotes ?
        return mysql_real_escape_string($str, $this->conn);
    }
    
    public function quote_column_name($name)
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
        while ($row = $this->fetch($rs, false)) $tables[] = $row[0];
        return $tables;
    }
    
    public function table_exists($name)
    {
        return in_array($name, $this->tables());
    }
    
    public function create_table($name, $table_def, $options = array())
    {
        $sql = "CREATE TABLE $name (";
        $sql.= $table_def->to_sql();
        $sql.= ")";
        
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
        $sql = "ALTER TABLE $table_name ADD $column_name ".self::type_to_sql($type, $options['limit']);
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
        .self::type_to_sql($type, $options['limit']);
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
    
    public function type_to_sql($type, $limit = null)
    {
        $native = $this->native_db_types[$type];
        if (!is_array($native)) $sql = $native;
        else
        {
            if ($limit === null && isset($native['limit'])) $limit = $native['limit'];
            $sql = $native['name'];
            if ($limit !== null) $sql.= "($limit)";
        }
        return $sql;
    }
    
    public function add_column_options($sql, $type, $options)
    {
        if ($options['default'] !== null)
            $sql.= ' DEFAULT '.$this->quote($options['default'], $type);
        if ($options['null'] === false) $sql.= ' NOT NULL';
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
