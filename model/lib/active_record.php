<?php

class SAssociationTypeMismatch extends SException { }

class SActiveRecord extends SRecord
{
    public $tableName        = Null;
    public $sqlMapping       = array();
    public $identityField    = 'id';
    public $inheritanceField = 'type';
    public $recordTimestamps = False;
    
    protected $db = Null;
    protected $metaAttributes = array('created_on', 'updated_on');
    protected $assocMethods = array();
    protected $newRecord = False;
    
    public function __construct($values = Null, $dontInitAssocs=false, $newRecord = True)
    {
        $this->db = SDatabase::getInstance();
        if ($this->tableName == Null) $this->tableName = SInflection::pluralize(strtolower(get_class($this)));
        if (empty($this->attributes)) $this->attributes = SActiveStore::getAttributes($this->tableName);
        else $this->initAttributes();
        
        $this->initValues();
        if ($values != Null && is_array($values)) $this->populate($values);
        
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
    
    public static function establishConnection($config = array())
    {
        // TODO : cette méthode établit la connec et une méthode self::connection()
        // permet de la retriever. Ainsi on peut gérer un pool de connecs.
        // Cf connection_specification.rb
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
    
    public function save()
    {
        if (!$this->isValid()) return false;
        $this->setState('beforeSave');
        if ($this->isNewRecord()) $this->create();
        else $this->update();
        $this->setState('afterSave');
        return true;
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
    
    public function readId()
    {
        return $this->values[$this->identityField];
    }
    
    public function writeId($value)
    {
        $this->values[$this->identityField] = $value;
    }
    
    public function create()
    {
        $this->setState('beforeCreate');
        $sql = 'INSERT INTO '.$this->tableName.' '.
               $this->prepareSqlSet();
        $this->id = $this->db->insert($sql);
        $this->newRecord = False;
        $this->setState('afterCreate');
    }
    
    public function update()
    {
        $this->setState('beforeUpdate');
        $sql = 'UPDATE '.$this->tableName.' '.
               $this->prepareSqlSet().
               ' WHERE '.$this->identityField.' = \''.$this->id.'\'';
        $this->db->update($sql);
        $this->setState('afterUpdate');
    }
    
    public function delete()
    {
        $this->setState('beforeDelete');
        if ($this->isNewRecord()) return false;
        $sql = 'DELETE FROM '.$this->tableName.
               ' WHERE '.$this->identityField.' = \''.$this->id.'\'';
        $this->db->update($sql);
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
    
    public function registerAssociationMethod($virtualMethod, $assoc, $method)
    {
        $this->assocMethods[$virtualMethod] = array('assoc' => $assoc, 'method' => $method);
    }
    
    protected function readAssociation($name)
    {
        if ($this->relationships[$name]['type'] == 'to_one' || $this->relationships[$name] == 'to_one')
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
                $set[] = "$column = ".$this->db->quote($this->$column, $attr->type);
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
    
    private function initAssociations()
    {
        foreach($this->relationships as $name => $options)
        {
            $this->assocs[$name] = SAssociationProxy::getInstance($this, $name, $options, $this->sqlMapping($name));
        }
    }
    
    private function sqlMapping($attr)
    {
        if (isset($this->sqlMapping[$attr])) return $this->sqlMapping[$attr];
        else return array();
    }
}

?>
