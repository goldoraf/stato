<?php

class SAssociationProxy
{
    public static function retrieveInstances($meta, $relationships)
    {
        $associations = array();
        foreach ($relationships as $name => $options)
        {
            $options = self::getOptions($meta, $name, $options);
            $class = 'S'.SInflection::camelize($options['assoc_type']).'Association';
            $associations[$name] = new $class($options);
        }
        return $associations;
    }
    
    public static function getOptions($meta, $name, $options)
    {
        if (!is_array($options))
        {
            $type = $options;
            $options = array();
            $options['assoc_type'] = $type;
        }
        
        $options['owner_class'] = $meta->class;
        
        if (!isset($options['assoc_type'])) throw new SException('Type of relationship is required.');
        
        if (!isset($options['class_name']))
        {
            if ($options['assoc_type'] == 'has_many' || $options['assoc_type'] == 'many_to_many') 
                $options['class_name'] = SInflection::camelize(SInflection::singularize($name));
            else 
                $options['class_name'] = SInflection::camelize($name);
        }
        
        $dest = $options['class_name'];
        
        if (!class_exists($dest))
            SDependencies::requireDependency('models', $dest, $meta->class);
        
        $assocMethod = SInflection::camelize($options['assoc_type']);
        
        return self::$assocMethod($meta, $name, $dest, $options);
    }
    
    public static function hasMany($meta, $name, $dest, $options)
    {
        self::assertValidOptions($options, array('foreign_key', 'dependent', 'through'));
        
        if (isset($options['through']))
        {
            $options['assoc_type'] = 'has_many_through';
            $throughClass = SInflection::camelize(SInflection::singularize($options['through']));
            $throughMeta = SMetaManager::retrieve($throughClass);;
            $options['through_table_name'] = $throughMeta->tableName;
            $options['through_foreign_key'] = $meta->underscored.'_id';
            
            if (isset($throughMeta->relationships[SInflection::underscore($dest)]))
                $r = $throughMeta->relationships[SInflection::underscore($dest)];
            elseif (isset($throughMeta->relationships[SInflection::underscore(SInflection::pluralize($dest))]))
                $r = $throughMeta->relationships[SInflection::underscore(SInflection::pluralize($dest))];
            
            if ($r == 'belongs_to' || $r['assoc_type'] == 'belongs_to')
                $options['foreign_key'] = SInflection::underscore($dest).'_id';
            elseif ($r == 'has_many' || $r['assoc_type'] == 'has_many')
                $options['foreign_key'] = $throughMeta->underscored.'_id';
        }
        elseif (!isset($options['foreign_key'])) 
            $options['foreign_key'] = $meta->underscored.'_id';
        
        return $options;
    }
    
    public static function belongsTo($meta, $name, $dest, $options)
    {
        self::assertValidOptions($options, array('foreign_key'));
        
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = SInflection::underscore($dest).'_id';
        
        return $options;
    }
    
    public static function manyToMany($meta, $name, $dest, $options)
    {
        self::assertValidOptions($options, array('foreign_key', 'association_foreign_key', 'join_table'));
        
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = $meta->underscored.'_id';
        if (!isset($options['association_foreign_key'])) 
            $options['association_foreign_key'] = SInflection::underscore($dest).'_id';
        if (!isset($options['join_table']))
            $options['join_table'] = self::joinTableName($meta->class, $dest);
        
        return $options;
    }
    
    public static function hasOne($meta, $name, $dest, $options)
    {
        self::assertValidOptions($options, array('foreign_key'));
        
        if (!isset($options['foreign_key']))
            $options['foreign_key'] = $meta->underscored.'_id';
        
        return $options;
    }
    
    private static function joinTableName($firstName, $secondName)
    {
        $firstName  = self::undecoratedTableName($firstName);
        $secondName = self::undecoratedTableName($secondName);
        
        if ($firstName < $secondName)
            $tableName = "${firstName}_${secondName}";
        else
            $tableName = "${secondName}_${firstName}";
            
        if (SActiveRecord::$tableNamePrefix !== null)
            $tableName = SActiveRecord::$tableNamePrefix.'_'.$tableName;
        if (SActiveRecord::$tableNameSuffix !== null)
            $tableName.= '_'.SActiveRecord::$tableNameSuffix;
            
        return $tableName;
    }
    
    private static function undecoratedTableName($className)
    {
        return SInflection::pluralize(SInflection::underscore($className));
    }
    
    private static function assertValidOptions($options, $validOptions)
    {
        $validOptions = array_merge(array('assoc_type', 'class_name', 'owner_class'), $validOptions);
        foreach(array_keys($options) as $key)
        {
            if (!in_array($key, $validOptions))
                throw new SException($key.' is not a valid mapping option.');
        }
    }
}

?>
