<?php

namespace Stato\Orm;

use ArrayAccess;

class Database implements ArrayAccess
{
    private static $instance;
    
    private $tables;
    private $tablesToClasses;
    private $classesToMappers;
    private $connection;
    
    public static function getInstance()
    {
        return self::$instance;
    }
    
    public function __construct(Connection $conn = null)
    {
        if (!is_null($conn)) $this->connect($conn);
        $this->tables = array();
        $this->tablesToClasses = array();
        $this->classesToMappers = array();
        
        self::$instance = $this;
    }
    
    public function offsetGet($tableName)
    {
        return $this->from($tableName);
    }
    
    public function offsetExists($key)
    {
        
    }
    
    public function offsetSet($key, $value)
    {
        
    }
    
    public function offsetUnset($key)
    {
        
    }
    
    public function connect($conn)
    {
        if ($conn instanceof Connection)
            $this->connection = $conn;
        elseif (is_array($conn))
            $this->connection = new Connection($conn);
    }
    
    public function getConnection()
    {
        if (!isset($this->connection))
            throw new UnboundConnectionError(__CLASS__);
        
        return $this->connection;
    }
    
    public function from($tableName)
    {
        return new Dataset($this->getTable($tableName), $this->getConnection(), $this->getMapperForTable($tableName));
    }
    
    public function addTable(Table $table)
    {
        $this->tables[$table->getName()] = $table;
    }
    
    public function getTable($tableName)
    {
        if (!array_key_exists($tableName, $this->tables))
            $this->tables[$tableName] = $this->getConnection()->reflectTable($tableName);
        
        return $this->tables[$tableName];
    }
    
    public function map($className, $tableName, array $options = array())
    {
        $mapper = new Mapper($className, $this->getTable($tableName), $this->getConnection(), $options);
        $this->addMapper($mapper);
        return $mapper;
    }
    
    public function addMapper(Mapper $mapper)
    {
        $this->tablesToClasses[$mapper->getTableName()] = $mapper->getClassName();
        $this->classesToMappers[$mapper->getClassName()] = $mapper;
    }
    
    public function getMapper($className)
    {
        if (!array_key_exists($className, $this->classesToMappers))
            throw new Exception("Mapper not found for '$className'");
        
        return $this->classesToMappers[$className];
    }
    
    public function getMapperForTable($tableName)
    {
        if (!array_key_exists($tableName, $this->tablesToClasses)) return null;
        return $this->classesToMappers[$this->tablesToClasses[$tableName]];
    }
}