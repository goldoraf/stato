<?php

class SAssociationTypeMismatch extends SException { }

class SActiveRecordMeta
{
    public $class            = null;
    public $underscored      = null;
    public $tableName        = null;
    public $identityField    = 'id';
    public $inheritanceField = 'type';
    public $attributes       = array();
    public $relationships    = array();
    
    private static $cache = array();
    
    public static function addManagerToClass($class)
    {
        $ref = new ReflectionClass($class);
        if ($ref->hasProperty('objects')) $ref->setStaticPropertyValue('objects', new SManager($class));
    }
    
    public static function retrieve($class)
    {
        if (!isset(self::$cache[$class]))
        {
            $metaClass = $class.'Meta';
            self::$cache[$class] = new SActiveRecordMeta($class);
        }
        return self::$cache[$class];
    }
    
    public static function resetMetaInformation($class)
    {
        unset(self::$cache[$class]);
    }
    
    public function __construct($class)
    {
        $this->class = $class;
        $this->underscored = SInflection::underscore($class);
        $this->getMetaFromClass();
        if ($this->tableName === null) $this->resetTableName();
        $this->attributes = array_merge(
            SActiveRecord::connection()->columns($this->tableName),
            $this->instantiateAssociations()
        );
    }
    
    public function resetTableName()
    {
        if (($parent = $this->descendsFrom()) == 'SActiveRecord')
            $this->tableName = SInflection::pluralize(SInflection::underscore($this->class));
        else
            $this->tableName = SInflection::pluralize(SInflection::underscore($parent));
            
        if (SActiveRecord::$tableNamePrefix !== null)
            $this->tableName = SActiveRecord::$tableNamePrefix.'_'.$this->tableName;
        if (SActiveRecord::$tableNameSuffix !== null)
            $this->tableName.= '_'.SActiveRecord::$tableNameSuffix;
    }
    
    public function descendsFrom()
    {
        $ref = new ReflectionClass($this->class);
        return $ref->getParentClass()->getName();
    }
    
    protected function instantiateAssociations()
    {
        $assocs = array();
        foreach ($this->relationships as $name => $options) 
            $assocs[$name] = new SAssociation(SAssociationMeta::getInstance($this, $name, $options));
        return $assocs;
    }
    
    protected function getMetaFromClass()
    {
        $ref = new ReflectionClass($this->class);
        $props = array('tableName', 'identityField', 'inheritanceField', 'relationships');
        foreach ($props as $p) 
            if ($ref->hasProperty($p)) $this->$p = $ref->getStaticPropertyValue($p);
    }
}

class SActiveRecord extends SObservable implements ArrayAccess
{
    public $errors        = array();
    public $attrRequired  = array();
    public $attrProtected = array();
    public $attrUnique    = array();
    public $validations   = array();
    
    public $recordTimestamps = False;
    
    public static $tableNamePrefix = null;
    public static $tableNameSuffix = null;
    
    protected static $conn = null;
    protected $values      = array();
    protected $meta        = null;
    
    public function __construct($values = null)
    {
        $this->meta = SActiveRecordMeta::retrieve(get_class($this));
        $this->ensureProperType();
        if ($values != null && is_array($values)) $this->populate($values);
    }
    
    public function __get($name)
    {
        $accMethod = 'read'.ucfirst($name);
        if (method_exists($this, $accMethod)) return $this->$accMethod();
        else return $this->readAttribute($name);
    }
    
    public function __set($name, $value)
    {
        $accMethod = 'write'.ucfirst($name);
        if (method_exists($this, $accMethod)) return $this->$accMethod($value);
        else return $this->writeAttribute($name, $value);
    }
    
    public function __toString()
    {
        $str = '';
        foreach($this->values as $key => $value)
        {
            if ($value === True) $value = 'True';
            if ($value === False) $value = 'False';
            if ($value === Null) $value = 'Null';
            $str.= "$key = $value\n";
        }
        return '['.get_class($this)."]\n".$str;
    }
    
    public function __repr()
    {
        return $this->id;
    }
    
    public function offsetExists($offset)
    {
        return (isset($this->values[$offset]));
    }
    
    public function offsetGet($offset)
    {
        return $this->values[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = $value;
    }
    
    public function offsetUnset($offset)
    {
        $this->values[$offset] = null;
    }
    
    /**
     * Sets all attributes at once by passing in an array with keys matching the
     * attribute names.
     */
    public function populate($values=array())
    {
        $multiParamsAttributes = array();
        
        foreach($values as $key => $value)
        {
            if (is_array($value)) $multiParamsAttributes[$key] = $value;
            elseif (!in_array($key, array_merge($this->attrProtected, array_keys($this->meta->relationships))))
            {
                if (!is_object($value) && $value !== null) $this->$key = stripslashes($value);
                else $this->$key = $value;
            }
        }
        
        if (!empty($multiParamsAttributes)) $this->assignMultiparamsAttributes($multiParamsAttributes);
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
        $sql = 'DELETE FROM '.$this->meta->tableName.
               ' WHERE '.$this->meta->identityField.' = \''.$this->id.'\'';
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
        $id = $this->readId();
        if ($id !== null)
            return !$this->conn()->selectOne("SELECT 1 FROM {$this->meta->tableName} 
                                             WHERE {$this->meta->identityField}=$id LIMIT 1");
        return true;
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
     * Overwrite this method for check validations on all saves
     */
    public function validate()
    {
    
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
    
    protected function attrExists($name)
    {
        return array_key_exists($name, $this->meta->attributes);
    }
    
    protected function readAttribute($name)
    {
        if (!$this->attrExists($name)) return;
        if (!isset($this->values[$name]))
            $this->values[$name] = $this->meta->attributes[$name]->defaultValue($this);
        
        return $this->values[$name];
    }
    
    protected function writeAttribute($name, $value)
    {
        if (!$this->attrExists($name)) return;
        $this->values[$name] = $this->meta->attributes[$name]->typecast($this, $value);
    }
    
    protected function readId()
    {
        return $this->readAttribute($this->meta->identityField);
    }
    
    protected function writeId($value)
    {
        $this->writeAttribute($this->meta->identityField, $value);
    }
    
    /**
     * Creates string values for all attributes that needs more than one single parameter
     * (such as Dates).
     */
    protected function assignMultiparamsAttributes($params)
    {
        $errors = array();
        foreach($params as $key => $value)
        {
            $type = $this->meta->attributes[$key]->type;
            switch ($type)
            {
                case 'date':
                    $this->$key = $value['year'].'-'.$value['month'].'-'.$value['day'];
                    break;
                case 'datetime':
                    $this->$key = $value['year'].'-'.$value['month'].'-'.$value['day']
                                  .' '.$value['hour'].':'.$value['min'].':'.$value['sec'];
                    break;
            }
        }
    }
    
    protected function prepareSqlSet()
    {
        $set = array();
        foreach($this->meta->attributes as $column => $attr)
            if (!array_key_exists($column, $this->meta->relationships))
                $set[] = "$column = ".$this->conn()->quote($this->$column, $attr->type);
        
        return 'SET '.join(',', $set);
    }
    
    protected function saveWithTimestamps()
    {
        $t = SDateTime::today();
        if ($this->isNewRecord())
            if ($this->attrExists('created_on')) $this->values['created_on'] = $t;
        
        if ($this->attrExists('updated_on')) $this->values['updated_on'] = $t;
    }
    
    protected function beforeCreate() {}
    
    protected function afterCreate() {}
    
    protected function beforeUpdate() {}
    
    protected function afterUpdate() {}
    
    protected function beforeSave()
    {
        if ($this->recordTimestamps) $this->saveWithTimestamps();
        foreach($this->meta->relationships as $k => $v) 
                $this->$k->beforeOwnerSave();
    }
    
    protected function afterSave()
    {
        foreach($this->meta->relationships as $k => $v) 
            $this->$k->afterOwnerSave();
    }
    
    protected function beforeDelete()
    {
        foreach($this->meta->relationships as $k => $v) 
            $this->$k->beforeOwnerDelete();
    }
    
    protected function afterDelete() {}
    
    protected function beforeValidate() {}
    
    protected function afterValidate() {}
    
    protected function runValidations($method)
    {
        foreach (array_keys($this->values) as $key)
            SValidation::validateAttribute($this, $key, $method);
    }
    
    private function createRecord()
    {
        $this->setState('beforeCreate');
        $sql = 'INSERT INTO '.$this->meta->tableName.' '.
               $this->prepareSqlSet();
        $this->id = $this->conn()->insert($sql);
        $this->setState('afterCreate');
    }
    
    private function updateRecord()
    {
        $this->setState('beforeUpdate');
        $sql = 'UPDATE '.$this->meta->tableName.' '.
               $this->prepareSqlSet().
               ' WHERE '.$this->meta->identityField.' = \''.$this->id.'\'';
        $this->conn()->update($sql);
        $this->setState('afterUpdate');
    }
    
    private function ensureProperType()
    {
        if ($this->meta->descendsFrom() != 'SActiveRecord')
            $this->writeAttribute($this->meta->inheritanceField, SInflection::underscore(get_class($this)));
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
