<?php

class SAssociationTypeMismatch extends SException { }

class SActiveRecord extends SRecord
{
    public $tableName        = null;
    public $identityField    = 'id';
    public $inheritanceField = 'type';
    public $recordTimestamps = False;
    
    protected $metaAttributes = array('created_on', 'updated_on');
    protected $assocMethods = array();
    protected $newRecord = False;
    
    public static $tableNamePrefix = null;
    public static $tableNameSuffix = null;
    
    protected static $conn = null;
    
    public function __construct($values = null, $dontInitAssocs=false, $newRecord = True)
    {
        if ($this->tableName == null) $this->resetTableName();
        if (empty($this->attributes)) $this->attributes = SActiveStore::getAttributes($this->tableName);
        else $this->initAttributes();
        
        $this->initValues();
        $this->ensureProperType();
        if ($values != null && is_array($values)) $this->populate($values);
        
        $this->newRecord = $newRecord;
        
        if (!$dontInitAssocs) $this->initAssociations();
    }
    
    public function __call($methodMissing, $args)
    {
        if (isset($this->assocMethods[$methodMissing]))
        {
            $name   = $this->assocMethods[$methodMissing]['assoc'];
            $method = $this->assocMethods[$methodMissing]['method'];
            
            return $this->assocs[$name]->$method($args[0]);
        }
        return;
    }
    
    public function __repr()
    {
        return $this->id;
    }
    
    public function save()
    {
        if (!$this->isValid()) return false;
        $this->setState('beforeSave');
        if ($this->isNewRecord()) $this->createRecord();
        else $this->updateRecord();
        $this->setState('afterSave');
        return true;
    }
    
    public function delete()
    {
        $this->setState('beforeDelete');
        if ($this->isNewRecord()) return false;
        $sql = 'DELETE FROM '.$this->tableName.
               ' WHERE '.$this->identityField.' = \''.$this->id.'\'';
        $this->conn()->update($sql);
        $this->setState('afterDelete');
    }
    
    public function updateAttributes($values)
    {
        $this->populate($values);
        return $this->save();
    }
    
    public function updateAttribute($name, $value)
    {
        $this->$name = $value;
        return $this->save();
    }
    
    public function isNewRecord()
    {
        return $this->newRecord;
    }
    
    public function isValid()
    {
        $this->errors = array();
        $this->setState('beforeValidate');
        $this->runValidations('save');
        $this->validate();
        if ($this->isNewRecord())
        {
            $this->runValidations('create');
            $this->validateOnCreate();
        }  
        else
        {
            $this->runValidations('update');
            $this->validateOnUpdate();
        }
        $this->setState('afterValidate');
        return empty($this->errors);
    }
    
    /**
     * Overwrite this method for check validations on creation
     */
    public function validateOnCreate()
    {
    
    }
    
    /**
     * Overwrite this method for check validations on updates
     */
    public function validateOnUpdate()
    {
    
    }
    
    public function contentAttributes()
    {
        $attributes = array();
        foreach($this->attributes as $key => $attr)
        {
            if ($key != $this->identityField && !preg_match('/_id|_count/', $key)
                && !in_array($key, $this->metaAttributes))
            {
                $attributes[$key] = $attr;
            }
        }
        return $attributes;
    }
    
    public function registerAssociationMethod($virtualMethod, $assoc, $method)
    {
        $this->assocMethods[$virtualMethod] = array('assoc' => $assoc, 'method' => $method);
    }
    
    public function setAssocAsLoaded($name)
    {
        $this->assocs[$name]->setAsloaded();
    }
    
    protected function readId()
    {
        return $this->readAttribute($this->identityField);
    }
    
    protected function writeId($value)
    {
        $this->writeAttribute($this->identityField, $value);
    }
    
    protected function readAssociation($name)
    {
        $rel  = $this->relationships[$name];
        $type = (is_array($rel)) ? $rel['assoc_type'] : $rel;
        if ($type == 'belongs_to' || $type == 'has_one')
            return $this->assocs[$name]->read();
        else
            return $this->assocs[$name];
    }
    
    protected function writeAssociation($name, $value)
    {
        return $this->assocs[$name]->replace($value);
    }
    
    protected function prepareSqlSet()
    {
        $set = array();
        foreach($this->attributes as $column => $attr)
        {
                $set[] = "$column = ".$this->conn()->quote($this->$column, $attr->type);
        }
        return 'SET '.join(',', $set);
    }
    
    protected function saveWithTimestamps()
    {
        $t = SDateTime::today();
        if ($this->isNewRecord())
        {
            if ($this->attrExists('created_on')) $this->values['created_on'] = $t->__toString();
        }
        if ($this->attrExists('updated_on')) $this->values['updated_on'] = $t->__toString();
    }
    
    protected function beforeCreate() {}
    
    protected function afterCreate() {}
    
    protected function beforeUpdate() {}
    
    protected function afterUpdate() {}
    
    protected function beforeSave()
    {
        if ($this->recordTimestamps) $this->saveWithTimestamps();
        foreach($this->assocs as $assoc) $assoc->beforeOwnerSave();
    }
    
    protected function afterSave()
    {
        foreach($this->assocs as $assoc) $assoc->afterOwnerSave();
    }
    
    protected function beforeDelete()
    {
        foreach($this->assocs as $assoc) $assoc->beforeOwnerDelete();
    }
    
    protected function afterDelete() {}
    
    protected function beforeValidate() {}
    
    protected function afterValidate() {}
    
    private function createRecord()
    {
        $this->setState('beforeCreate');
        $sql = 'INSERT INTO '.$this->tableName.' '.
               $this->prepareSqlSet();
        $this->id = $this->conn()->insert($sql);
        $this->newRecord = False;
        $this->setState('afterCreate');
    }
    
    private function updateRecord()
    {
        $this->setState('beforeUpdate');
        $sql = 'UPDATE '.$this->tableName.' '.
               $this->prepareSqlSet().
               ' WHERE '.$this->identityField.' = \''.$this->id.'\'';
        $this->conn()->update($sql);
        $this->setState('afterUpdate');
    }
    
    private function initAssociations()
    {
        foreach($this->relationships as $name => $options)
            $this->assocs[$name] = SAssociationProxy::getInstance($this, $name, $options);
    }
    
    private function ensureProperType()
    {
        if ($this->descendsFrom != 'SActiveRecord')
            $this->writeAttribute($this->inheritanceField, SInflection::underscore(get_class($this)));
    }
    
    private function resetTableName()
    {
        if (($class = $this->descendsFrom()) == 'SActiveRecord')
            $this->tableName = SInflection::pluralize(SInflection::underscore(get_class($this)));
        else
            $this->tableName = SInflection::pluralize(SInflection::underscore($class));
            
        if (self::$tableNamePrefix !== null)
            $this->tableName = self::$tableNamePrefix.'_'.$this->tableName;
        if (self::$tableNameSuffix !== null)
            $this->tableName.= '_'.self::$tableNameSuffix;
    }
    
    private function descendsFrom()
    {
        $class = new ReflectionClass(get_class($this));
        return $class->getParentClass()->getName();
    }
    
    /**
     * CONNECTION MANAGEMENT ===================================================
     **/
    public static function connection()
    {
        if (!isset(self::$conn)) self::establishConnection();
        return self::$conn;
    }
    
    protected function conn()
    {
        return self::connection();
    }
    
    protected static function establishConnection($config = array())
    {
        $config = include(ROOT_DIR.'/conf/database.php');
        $driverClass = 'S'.$config[APP_MODE]['driver'].'Driver';
        if (!class_exists($driverClass)) 
            throw new SException('Database driver not found !');
        
        self::$conn = new $driverClass($config[APP_MODE]);
        self::$conn->connect();
    }
}

?>
