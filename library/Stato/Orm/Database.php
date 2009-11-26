<?php

namespace Stato\Orm;

class Database implements \ArrayAccess
{
    private $tables;
    private $mappers;
    private $connection;
    
    public function __construct(Connection $conn = null)
    {
        if (!is_null($conn)) $this->connect($conn);
        $this->tables = array();
        $this->mappers = array();
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
        return new Dataset($this->getTable($tableName), $this->getConnection(), $this->getMapper($tableName));
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
        $mapper = new Mapper($className, $this->getTable($tableName), $options);
        $this->addMapper($mapper);
    }
    
    public function addMapper(Mapper $mapper)
    {
        $this->mappers[$mapper->getTableName()] = $mapper;
    }
    
    public function getMapper($tableName)
    {
        if (!array_key_exists($tableName, $this->mappers)) return null;
        return $this->mappers[$tableName];
    }
}