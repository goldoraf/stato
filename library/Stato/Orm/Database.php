<?php

namespace Stato\Orm;

class Database implements \ArrayAccess
{
    private $tables;
    private $connection;
    
    public function __construct(Connection $conn = null)
    {
        if (!is_null($conn)) $this->connect($conn);
        $this->tables = array();
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
    
    public function from($tableName)
    {
        return new Dataset($this->getTable($tableName), $this->getConnection());
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
    
    public function getConnection()
    {
        if (!isset($this->connection))
            throw new UnboundConnectionError(__CLASS__);
        
        return $this->connection;
    }
}