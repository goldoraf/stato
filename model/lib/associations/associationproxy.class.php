<?php

class AssociationProxy
{
    public static function getInstance($owner, $name, $options, $mapping = array())
    {
        list($assocType, $dest, $assocOptions) = self::getOptions($owner, $name, $options, $mapping);
        if ($options['type'] == 'to_many') self::registerToManyMethods($owner, $name, $dest);
        else self::registerToOneMethods($owner, $name, $dest);
        $assocClass = ucfirst($assocType).'Association';
        return new $assocClass($owner, $name, $dest, $assocOptions);
    }
    
    public static function getOptions($owner, $name, $options, $mapping = array())
    {
        if (!is_array($options))
        {
            $type = $options;
            $options = array();
        }
        elseif (!isset($options['type'])) throw new Exception('Type of relationship is required.');
        else $type = $options['type'];
        
        if (!isset($options['dest']))
        {
            if ($options['type'] == 'to_many') $options['dest'] = Inflection::singularize($name);
            else $options['dest'] = $name;
        }
        
        $dest = $options['dest'];
        // we instanciate the dest class without associations to avoid an infinite loop
        if (!class_exists($dest))
            require_once(Context::inclusionPath().'/models/'.strtolower($dest).'.class.php');
            
        $destInstance = new $dest(Null, True);
        
        $mapping['table_name']  = $destInstance->tableName;
        $mapping['primary_key'] = $destInstance->identityField;
        
        if (isset($options['inverse']) && $options['inverse'] == true) $inverse = true;
        else $inverse = false;
        
        if (!isset($mapping['assoc_type']))
            $assocType = self::findAssocType($type, $destInstance, get_class($owner), $inverse);
        else
            $assocType = $mapping['assoc_type'];
        
        return array($assocType, $dest, self::$assocType($owner, $name, $dest, $mapping));
    }
    
    public static function hasMany($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key'));
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = strtolower(get_class($owner)).'_id';
        
        return $options;
    }
    
    public static function belongsTo($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key'));
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = strtolower($dest).'_id';
        
        return $options;
    }
    
    public static function manyToMany($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key', 'association_foreign_key', 'join_table'));
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = strtolower(get_class($owner)).'_id';
        if (!isset($options['association_foreign_key'])) 
            $options['association_foreign_key'] = strtolower($dest).'_id';
        if (!isset($options['join_table']))
            $options['join_table'] = self::joinTableName($owner->tableName, $options['table_name']);
        
        return $options;
    }
    
    public static function oneToOne($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key'));
        if (!isset($options['foreign_key']))
            $options['foreign_key'] = strtolower(get_class($owner)).'_id';
        if (!isset($options['association_foreign_key'])) 
            $options['association_foreign_key'] = strtolower($dest).'_id';
        
        return $options;
    }
    
    private static function registerToOneMethods($owner, $name, $dest)
    {
        $owner->registerAssociationMethod($name, $name, 'read');
        $owner->registerAssociationMethod('build'.$dest, $name, 'build');
        $owner->registerAssociationMethod('create'.$dest, $name, 'create');
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
    
    private static function findAssocType($relationType, $destInstance, $ownerClass, $hasInverse = False)
    {
        if ($hasInverse && ($inverseType = self::findInverseType($destInstance, $ownerClass)) === false)
                throw new Exception('Could not find inverse relationship.');
        
        if ($relationType == 'to_one')
        {
            if ($hasInverse && $inverseType == 'to_one') return 'oneToOne';
            return 'belongsTo';
        }
        elseif ($relationType == 'to_many')
        {
            if ($hasInverse && $inverseType == 'to_many') return 'manyToMany';
            return 'hasMany';
        }
    }
    
    private static function findInverseType($destInstance, $ownerClass)
    {
        $type = false;
        foreach ($destInstance->relationships as $relName => $relOptions)
        {
            if ((isset($relOptions['dest']) && $relOptions['dest'] == $ownerClass)
                || $relName == $ownerClass || Inflection::singularize($relName) == $ownerClass)
            {
                $type = $relOptions['type'];
                break;
            }
        }
        
        return $type;
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
        $validOptions = array_merge(array('table_name', 'primary_key', 'assoc_type'), $validOptions);
        foreach(array_keys($options) as $key)
        {
            if (!in_array($key, $validOptions))
                throw new Exception($key.' is not a valid mapping option.');
        }
    }
}

?>
