<?php

class SAssociationProxy
{
    public static function getInstance($owner, $name, $options)
    {
        $options = self::getOptions($owner, $name, $options);
        
        $assocClass = 'S'.SInflection::camelize($options['assoc_type']).'Association';
        return new $assocClass($owner, $name, $options['class_name'], $options);
    }
    
    public static function getOptions($owner, $name, $options)
    {
        if (!is_array($options))
        {
            $type = $options;
            $options = array();
            $options['assoc_type'] = $type;
        }
        
        if (!isset($options['assoc_type'])) throw new SException('Type of relationship is required.');
        
        if (!isset($options['class_name']))
        {
            if ($options['assoc_type'] == 'has_many' || $options['assoc_type'] == 'many_to_many') 
                $options['class_name'] = SInflection::singularize($name);
            else 
                $options['class_name'] = $name;
        }
        
        $dest = $options['class_name'];
        // we instanciate the dest class without associations to avoid an infinite loop
        if (!class_exists($dest))
            SDependencies::requireDependency('models', $dest, get_class($owner));
        
        $destInstance = new $dest(Null, True);
        
        $options['table_name']  = $destInstance->tableName;
        $options['primary_key'] = $destInstance->identityField;
        
        $assocMethod = SInflection::camelize($options['assoc_type']);
        
        return self::$assocMethod($owner, $name, $dest, $options);
    }
    
    public static function hasMany($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key'));
        self::registerToManyMethods($owner, $name, $dest);
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = strtolower(get_class($owner)).'_id';
        
        return $options;
    }
    
    public static function belongsTo($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key'));
        self::registerToOneMethods($owner, $name, $dest);
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = $dest.'_id';
        
        return $options;
    }
    
    public static function manyToMany($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key', 'association_foreign_key', 'join_table'));
        self::registerToManyMethods($owner, $name, $dest);
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = strtolower(get_class($owner)).'_id';
        if (!isset($options['association_foreign_key'])) 
            $options['association_foreign_key'] = $dest.'_id';
        if (!isset($options['join_table']))
            $options['join_table'] = self::joinTableName($owner->tableName, $options['table_name']);
        
        return $options;
    }
    
    public static function hasOne($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key'));
        self::registerToOneMethods($owner, $name, $dest);
        if (!isset($options['foreign_key']))
            $options['foreign_key'] = strtolower(get_class($owner)).'_id';
        
        return $options;
    }
    
    private static function registerToOneMethods($owner, $name, $dest)
    {
        $owner->registerAssociationMethod($name, $name, 'read');
        $owner->registerAssociationMethod('build'.ucfirst($dest), $name, 'build');
        $owner->registerAssociationMethod('create'.ucfirst($dest), $name, 'create');
    }
    
    private static function registerToManyMethods($owner, $name, $dest)
    {
        $owner->registerAssociationMethod($name, $name, 'read');
        $owner->registerAssociationMethod('count'.ucfirst($name), $name, 'count');
        $owner->registerAssociationMethod('build'.ucfirst($name), $name, 'build');
        $owner->registerAssociationMethod('create'.ucfirst($name), $name, 'create');
        $owner->registerAssociationMethod('delete'.ucfirst($name), $name, 'delete');
        $owner->registerAssociationMethod('clear'.ucfirst($name), $name, 'clear');
    }
    
    private static function joinTableName($firstName, $secondName)
    {
        if ($firstName < $secondName)
            return "${firstName}_${secondName}";
        else
            return "${secondName}_${firstName}";
    }
    
    private static function assertValidOptions($options, $validOptions)
    {
        $validOptions = array_merge(array('table_name', 'primary_key', 'assoc_type',
                                    'class_name'), $validOptions);
        foreach(array_keys($options) as $key)
        {
            if (!in_array($key, $validOptions))
                throw new SException($key.' is not a valid mapping option.');
        }
    }
}

?>
