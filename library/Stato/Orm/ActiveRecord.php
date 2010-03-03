<?php

namespace Stato\Orm;

class ActiveRecord
{
    protected static $tableName;
    
    protected static $dataset;
    protected static $mapper;
    
    protected $values;
    protected $new;
    
    public static function getTableName()
    {
        if (!isset(static::$tableName)) {
            static::setTableName(strtolower(get_called_class()).'s');
        }
        return static::$tableName;
    }
    
    public static function setTableName($tableName)
    {
        static::$tableName = $tableName;
    }
    
    public static function getDataset()
    {
        if (!isset(static::$dataset)) {
            if (!isset(static::$mapper)) static::initializeMapping();
            static::setDataset(Database::getInstance()->from(static::getTableName()));
        }
        return static::$dataset;
    }
    
    public static function setDataset(Dataset $dataset)
    {
        if (!isset(static::$tableName)) {
            static::setTableName($dataset::getTableName());
        }
        static::$dataset = $dataset;
    }
    
    public static function getMapper()
    {
        if (!isset(static::$mapper)) {
            static::initializeMapping();
        }
        return static::$mapper;
    }
    
    public static function get($id)
    {
        return static::getDataset()->get($id);
    }
    
    public static function filter()
    {
        return call_user_func_array(array(static::getDataset(), 'filter'), func_get_args());
    }
    
    public static function filterBy($values)
    {
        return static::getDataset()->filterBy($values);
    }
    
    protected static function initializeMapping()
    {
        static::$mapper = Database::getInstance()->map(get_called_class(), static::getTableName());
    }
    
    public function __construct(array $values = array())
    {
        $this->values = array();
        $this->new = true;
        $this->populate($values);
    }
    
    public function isNew()
    {
        return $this->new;
    }
    
    public function setAsLoaded()
    {
        $this->new = false;
    }
    
    public function save()
    {
        if ($this->isNew()) {
            static::getMapper()->insertObject($this);
            $this->new = false;
        } else {
            static::getMapper()->updateObject($this);
        }
    }
    
    public function delete()
    {
        static::getMapper()->deleteObject($this);
    }
    
    protected function populate(array $values)
    {
        
    }
}
