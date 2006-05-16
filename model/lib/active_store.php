<?php

class SActiveStore
{
    private static $tables = array();
    
    /**
     * Returns all the records matched by the options used.
     *
     * @param $conditions : SQL string or array('id = :id AND name = :name',
     *                                          array(':id' => 3, ':name' => "37signals"))
     * @param $options : array
     * The options are :
     * 'include' => array('user', 'photos') # forces the loading of relationships
     * 'order' => 'photo.id DESC'
     * 'limit' => 10
     * 'offset' => 20
     * 'joins' ?
     *
     * @return mixed
     **/
    public static function findAll($class, $conditions=null, $options=array())
    {
        if (isset($options['include']))
        {
            return self::findWithAssociations($class, $conditions, $options);
        }
        else
        {
            $sql = self::prepareSelect($class, $conditions, $options);
            return self::findBySql($class, $sql);
        }
    }
    
    /**
     * Returns the first record matched by the options used.
     *
     * @return mixed
     **/
    public static function findFirst($class, $conditions=null, $options=array())
    {
        $options = array_merge($options, array('limit' => 1));
        $set = self::findAll($class, $conditions, $options);
        return array_pop($set);
    }
    
    /**
     * Finds a record using a specific id.
     *
     * @param $value
     * @param $options
     * @return mixed
     **/
    public static function findByPk($class, $value, $options=array())
    {
        $instance = self::getInstance($class);
        
        if (is_array($value))
        {
            $condition = $instance->identityField.' IN ('.join(',', $value).')';
            return self::findAll($class, $condition, $options);
        }
        else
        {
            // la seule option utilisable est l'include
            $opt = array();
            if (isset($options['include']))
            {
                $opt['include'] = $options['include'];
                $condition = $instance->tableName.'.'.$instance->identityField.' = \''.$value.'\'';
            }
            else
            {
                $condition = $instance->identityField.' = \''.$value.'\'';
            }
            $set = self::findAll($class, $condition, $opt);
            if (empty($set)) return False;
            return array_pop($set);
        }
    }
    
    public static function findBySql($class, $sql)
    {
        $rs = self::connection()->select($sql);
        if (!$rs) return false;
        $set = array();
        while($row = $rs->fetch())
        {
            $set[] = self::getInstance($class, $row);
        }
        //if (count($set) == 1) return $set[0];
        return $set;
    }
    
    public static function create($class, $attributes = array())
    {
        $object = new $class($attributes);
        $object->save();
        return $object;
    }
    
    public static function update($class, $id, $attributes)
    {
        $object = self::findByPk($class, $id);
        $object->updateAttributes($attributes);
        return $object;
    }
    
    public static function updateAll($class, $updates, $conditions = Null)
    {
        $instance = self::getInstance($class);
        $sql = 'UPDATE '.$instance->tableName.' SET '.self::sanitizeSql($updates);
        $sql.= self::addConditions($conditions);
        self::connection()->update($sql);
    }
    
    public static function deleteAll($class, $conditions = Null)
    {
        $instance = self::getInstance($class);
        $sql = 'DELETE FROM '.$instance->tableName;
        $sql.= self::addConditions($conditions);
        self::connection()->update($sql);
    }
    
    public static function count($class, $conditions = null)
    {
        $instance = self::getInstance($class);
        $sql = 'SELECT COUNT(*) AS count FROM '.$instance->tableName;
        $sql.= self::addConditions($conditions);
        $rs = self::connection()->select($sql);
        $row = $rs->fetch();
        return $row['count'];
    }
    
    public static function insertId($class)
    {
        $instance = self::getInstance($class);
        $sql = 'SELECT MAX('.$instance->identityField.') AS max FROM '.$instance->tableName;
        $rs = self::connection()->select($sql);
        $row = $rs->fetch();
        return $row['max'];
    }
    
    public static function tableExists($tableName)
    {
        if (in_array($tableName, self::connection()->tables())) return true;
        return false;
    }
    
    public static function getAttributes($tableName)
    {
        if (!isset(self::$tables[$tableName])) 
            self::$tables[$tableName] = self::connection()->columns($tableName);
        
        return self::$tables[$tableName];
    }
    
    public static function resetAttributeInformation($tableName)
    {
        unset(self::$tables[$tableName]);
    }
    
    // must be public because Fixture uses it
    public static function arrayQuote($array)
    {
        foreach($array as $key => $value) $array[$key] = self::connection()->quote($value);
        return $array;
    }
    
    protected static function prepareSelect($class, $conditions=null, $options=array())
    {
        $instance = self::getInstance($class);
        $sql = 'SELECT * FROM '.$instance->tableName;
        $sql.= self::addConditions($conditions);
        if (isset($options['order'])) $sql.= ' ORDER BY '.$options['order'];
        if (isset($options['limit']))
        {
            $offset = 0;
            if (isset($options['offset'])) $offset = $options['offset'];
            $sql.= self::connection()->limit($options['limit'], $offset);
        }
        return $sql;
    }
    
    protected static function addConditions($conditions)
    {
        $segments = array();
        if ($conditions !== Null) $segments[] = self::sanitizeSql($conditions);
        //if (!self::descentsFromActiveEntity()) $segments[] = self::typeCondition();
        if (!empty($segments)) return ' WHERE ('.implode(") AND (", $segments).')';
        return;
    }
    
    // Returns a special SQL condition for inheritance hierarchies
    protected static function typeCondition()
    {
    
    }
    
    protected static function findWithAssociations($class, $conditions, $options)
    {
        $instance = self::getInstance($class, array(), True);
        $assocs = self::getIncludedAssociations($instance, $options['include']);
        $abbrv = self::getSchemaAbbreviations($instance->tableName, $assocs);
        $pkTable = self::getPkLookupTable($assocs, $abbrv);
        $pk = $instance->tableName.'_'.$instance->identityField;
        $sql = self::prepareSelectWithAssoc($instance, $conditions, $abbrv, $options, $assocs);
        $rs = self::connection()->select($sql);
        $records = array();
        $recordsInOrder = array();
        while($row = $rs->fetch())
        {
            $id = $row[$pk];
            if (!isset($records[$id]))
            {
                $recordsInOrder[] = $records[$id] = self::getInstance($class,
                            self::extractRecord($abbrv, $instance->tableName, $row));
            }
            foreach($assocs as $key => $assoc)
            {
                $records[$id]->$key->setAsLoaded();
                
                if (isset($row[$pkTable[$assoc['table_name']]]))
                {
                    $record = self::extractRecord($abbrv, $assoc['table_name'], $row);
                    if ($record)
                    {
                        if ($assoc['assoc_type'] == 'has_many' || $assoc['assoc_type'] == 'many_to_many')
                        {
                            $assoc = self::getInstance($assoc['class_name'], $record);
                            if (!$records[$id]->$key->contains($assoc)) $records[$id]->$key->add($assoc);
                        }
                        else
                        {
                            $records[$id]->$key = self::getInstance($assoc['class_name'], $record);
                        }
                    }
                }
            }
        }
        return $recordsInOrder;
    }
    
    // n'instancie pas les records dont ttes les values sont NULL !!!
    protected static function extractRecord($abbrv, $tableName, $row)
    {
        $record = array();
        $valid = false;
        foreach($row as $key => $value)
        {
            list($prefix, $column) = $abbrv[$key];
            if ($prefix == $tableName)
            {
                $record[$column] = $value;
                if ($value != null) $valid = true;
            }
        }
        if ($valid == true) return $record;
        return false;
    }
    
    private static function getSchemaAbbreviations($tableName, $assocs)
    {
        $assocs[] = array('table_name' => $tableName);
        $abbrv = array();
        foreach($assocs as $assoc)
        {
            $table = $assoc['table_name'];
            $columns = array_keys(self::connection()->columns($table));
            
            foreach($columns as $column)
                $abbrv[$table.'_'.$column] = array($table, $column);
        }
        return $abbrv;
    }
    
    private static function getPkLookupTable($assocs, $abbrvs)
    {
        $lookup = array();
        foreach($assocs as $assoc)
        {
            foreach($abbrvs as $abbrv => $options)
            {
                if ($options[0] == $assoc['table_name'] && $options[1] == $assoc['primary_key'])
                {
                    $lookup[$assoc['table_name']] = $abbrv;
                    break;
                }
            }
        }
        return $lookup;
    }
    
    private static function columnAliases($abbrv)
    {
        $aliases = array();
        foreach($abbrv as $alias => $arr)
        {
            $aliases[] = join($arr, '.').' AS '.$alias;
        }
        return join($aliases, ', ');
    }
    
    private static function getIncludedAssociations($instance, $include)
    {
        $associations = array();
        foreach($include as $assoc)
        {
            if (isset($instance->relationships[$assoc]))
                $associations[$assoc] = SAssociationProxy::getOptions($instance, $assoc, $instance->relationships[$assoc]);
        }
        return $associations;
    }
    
    private static function prepareSelectWithAssoc($instance, $conditions, $abbrv, $options, $associations)
    {
        $sql = 'SELECT '.self::columnAliases($abbrv).' FROM '.$instance->tableName;
        foreach($associations as $key => $assoc) $sql.= self::associationJoin($instance, $assoc);
        if ($conditions !== Null) $sql.= self::addConditions($conditions);
        if (isset($options['order'])) $sql.= ' ORDER BY '.$options['order'];
        return $sql;
    }
    
    private function associationJoin($instance, $association)
    {
        $options = $association;
        
        switch($options['assoc_type'])
        {
            case 'belongs_to':
                return ' LEFT OUTER JOIN '.$options['table_name'].' ON '.
                $options['table_name'].'.'.$options['primary_key'].' = '.
                $instance->tableName.'.'.$options['foreign_key'];
                
            case 'many_to_many':
                return ' LEFT OUTER JOIN '.$options['join_table'].' ON '.
                $options['join_table'].'.'.$options['foreign_key'].' = '.
                $instance->tableName.'.'.$instance->identityField.
                ' LEFT OUTER JOIN '.$options['table_name'].' ON '.
                $options['join_table'].'.'.$options['association_foreign_key'].' = '.
                $options['table_name'].'.'.$options['primary_key'];
                
            default: // has_one, has_many
                return ' LEFT OUTER JOIN '.$options['table_name'].' ON '.
                $options['table_name'].'.'.$options['foreign_key'].' = '.
                $instance->tableName.'.'.$instance->identityField;
        }
    }
    
    private static function sanitizeSql($sql)
    {
        if (!is_array($sql)) return $sql;
        else
        {
            $values = $sql[1];
            $stmt   = $sql[0];
            if (strpos($stmt, ':')) return self::replaceNamedBindVariables($stmt, $values);
            elseif (strpos($stmt, '?')) return self::replaceBindVariables($stmt, $values);
            else return vsprintf($stmt, $values);
        }
    }
    
    private static function replaceBindVariables($stmt, $values)
    {
        foreach ($values as $value) $stmt = preg_replace('/\?/i', self::connection()->quote($value), $stmt, 1);
        return $stmt;
    }
    
    private static function replaceNamedBindVariables($stmt, $values)
    {
        foreach ($values as $key => $value) $stmt = preg_replace('/'.$key.'/i', self::connection()->quote($value), $stmt, 1);
        return $stmt;
    }
    
    private static function getInstance($class, $values=array(), $dontInit=false)
    {
        if (class_exists($class))
        {
            return new $class($values, $dontInit, False);
        }
        throw new SException("SActiveStore : $class class not found.");
    }
    
    private static function connection()
    {
        return SActiveRecord::connection();
    }
}


?>
