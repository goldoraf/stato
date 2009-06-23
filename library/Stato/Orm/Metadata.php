<?php

namespace Stato\Orm;

class Metadata
{
    private $tables;
    private $connection;
    
    public function __construct(Connection $conn = null)
    {
        if (!is_null($conn)) $this->connect($conn);
        $this->tables = array();
    }
    
    public function connect($conn)
    {
        if ($conn instanceof Connection)
            $this->connection = $conn;
        elseif (is_array($conn))
            $this->connection = new Connection($conn);
    }
    
    public function addTable(Table $table)
    {
        $this->tables[$table->getName()] = $table;
    }
    
    public function getTable($tablename)
    {
        if (!array_key_exists($tablename, $this->tables))
            $this->tables[$tablename] = $this->getConnection()->reflectTable($tablename);
        
        return $this->tables[$tablename];
    }
    
    public function getConnection()
    {
        if (!isset($this->connection))
            throw new UnboundConnectionError(__CLASS__);
        
        return $this->connection;
    }
    
    /**
     * Load all available table definitions from the database
     */
    public function reflect()
    {
        
    }
}