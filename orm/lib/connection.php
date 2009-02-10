<?php

/**
 * Connects a PDO connection to a specific SQL dialect
 * 
 * @package Stato
 * @subpackage orm
 */
class Stato_Connection
{
    /**
     * User-provided configuration
     *
     * @var array
     */
    private $config = array();
    
    /**
     * PDO object
     *
     * @var PDO
     */
    private $connection = null;
    
    /**
     * Dialect object that defines the behavior of a specific DB
     *
     * @var mixed
     */
    private $dialect = null;
    
    private $driver = null;
    
    /**
     * Constructor
     *
     * @param array $config
     * @return void
     */
    public function __construct($config = array())
    {
        $this->config = array_merge($this->config, $config);
        $this->driver = ucfirst($this->config['driver']);
        $dialectClass = "Stato_{$this->driver}Dialect";
        $this->dialect = new $dialectClass();
        $this->connection = new PDO($this->dialect->getDsn($this->config), 
                                    $this->config['user'], $this->config['password']);
    }
    
    /**
     * Sets the logger to be used
     *
     * @param mixed $logger
     * @return void
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
    
    public function getPDOConnection()
    {
        return $this->connection;
    }
    
    public function getTableNames()
    {
        return $this->dialect->getTableNames($this->connection);
    }
    
    public function hasTable($tableName)
    {
        return in_array($tableName, $this->getTableNames());
    }
    
    public function reflectTable($tableName)
    {
        return $this->dialect->reflectTable($this->connection, $tableName);
    }
    
    public function createTable(Stato_Table $table)
    {
        return $this->connection->exec($this->dialect->createTable($table));
    }
    
    public function dropTable($table)
    {
        $tableName = (is_object($table)) ? $table->name : $table;
        return $this->connection->exec($this->dialect->dropTable($tableName));
    }
}

interface Stato_Dialect
{
    public function getDsn(array $params);
    
    public function getTableNames(PDO $connection);
    
    public function reflectTable(PDO $connection, $tableName);
    
    public function createTable(Stato_Table $table);
    
    public function dropTable($tableName);
    
    public function addColumn($tableName, Stato_Column $column);
    
    public function getColumnSpecification(Stato_Column $column);
    
    public function getDefaultValue(Stato_Column $column);
}
