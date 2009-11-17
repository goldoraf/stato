<?php

namespace Stato\Orm;

class Entity
{
    public static $metadata;
    
    protected static $mapper;
    protected static $connection;
    protected static $tablename;
    protected static $relations;
    
    public static function getMapper()
    {
        if (!isset(static::$mapper))
            static::$mapper = new EntityMapper(get_called_class());
        
        return static::$mapper;
    }
    
    public static function getTablename()
    {
        if (isset(static::$tablename)) return static::$tablename;
        return strtolower(get_called_class()).'s';
    }
    
    public static function getQuery()
    {
        return new Query(static::getMapper(), static::getConnection());
    }
    
    public static function setConnection(Connection $connection)
    {
        self::$connection = $connection;
    }
    
    public static function getConnection()
    {
        if (!isset(self::$connection))
            throw new Exception('Connection is not set'); // better message...
            
        return self::$connection;
    }
    
    public static function get($id)
    {
        return static::getQuery()->get($id);
    }
    
    public static function filter()
    {
        return call_user_func_array(array(static::getQuery(), 'filter'), func_get_args());
    }
    
    public static function filterBy($values)
    {
        return static::getQuery()->filterBy($values);
    }
    
    public function __construct(array $values = array())
    {
        self::getMapper()->populate($this, $values);
    }
    
    public function __get($property)
    {
        $property = self::getMapper()->getProperty($property);
        if (!$property)
            throw new Exception("Undefined property ${$property}");
    }
}

class EntityMapper extends Mapper
{
    public function __construct($entity)
    {
        if (!isset(Entity::$metadata))
            Entity::$metadata = new Metadata(Entity::getConnection());
            
        $relations = array(); // TODO
        
        $table = Entity::$metadata->getTable($entity::getTablename());
        
        parent::__construct($entity, $table, $relations);
    }
    
    public function setFetchMode(ResultProxy $res)
    {
        $res->setFetchMode(Connection::FETCH_ENTITY, $this->entity);
    }
}