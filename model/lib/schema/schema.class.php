<?php

class Schema
{
    public static function createTable($name, $tableDef, $options = array())
    {
        $sql = "CREATE TABLE $name (";
        $sql.= $tableDef->toSql();
        $sql.= ")";
        
        $db = Database::getInstance();
        $db->execute($sql);
    }
    
    public static function renameTable($oldName, $newName)
    {
    
    }
    
    public static function dropTable($name)
    {
        $db = Database::getInstance();
        $db->execute("DROP TABLE $name");
    }
    
    public static function addColumn($tableName, $columnName, $type, $options = array())
    {
        $sql = "ALTER TABLE $tableName ADD $columnName ".self::typeToSql($type, $options['limit']);
        $sql = self::addColumnOptions($sql, $options);
        $db = Database::getInstance();
        $db->execute($sql);
    }
    
    public static function changeColumn($tableName, $columnName, $type, $options = array())
    {
    
    }
    
    public static function renameColumn($tableName, $columnName, $newName)
    {
    
    }
    
    public static function addIndex($tableName, $columnName, $options = array())
    {
        if (isset($options['name'])) $indexName = $options['name'];
        else
        {
            if (!is_array($columnName)) $columnName = array($columnName);
            $indexName = "{$tableName}_".$columnName[0]."_index";
        }
        if (isset($options['unique'])) $indexType = 'UNIQUE';
        else $indexType = '';
        
        $db = Database::getInstance();
        $db->execute("CREATE {$indexType} INDEX {$indexName} ON {$tableName} (".implode(', ', $columnName).")");
    }
    
    public static function removeIndex($tableName, $options = array())
    {
        $db = Database::getInstance();
        $db->execute("DROP ".self::indexName($tableName, $options)." ON {$tableName}");
    }
    
    public static function indexName($tableName, $options = array())
    {
        if (isset($options['column']))
            return "{$tableName}_".$options['column']."_index";
        elseif (isset($options['name']))
            return $options['name'];
        else
            throw new Exception('You must specify the index name');
    }
    
    public static function typeToSql($type, $limit = Null)
    {
        $db = Database::getInstance();
        $native = $db->nativeDbTypes[$type];
        if ($limit === Null && isset($native['limit'])) $limit = $native['limit'];
        $sql = $native['name'];
        if ($limit !== Null) $sql.= "($limit)";
        return $sql;
    }
    
    public static function addColumnOptions($sql, $options)
    {
        $db = Database::getInstance();
        if ($options['default'] !== Null)
            $sql.= 'DEFAULT '.$db->quote($options['default'], $options['type']);
        if ($options['null'] === False) $sql.= 'NOT NULL';
        return $sql;
    }
}

?>
