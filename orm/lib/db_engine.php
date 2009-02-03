<?php

/**
 * Connects a pool of PDO connections to a specific SQL dialect
 * 
 * @package Stato
 * @subpackage orm
 */
class Stato_DbEngine
{
    /**
     * User-provided configuration
     *
     * @var array
     */
    private $config = array();
    
    /**
     * Database connection pool
     *
     * @var array
     */
    private $connectionPool = array();
    
    /**
     * Dialect object that defines the behavior of a specific DB
     *
     * @var mixed
     */
    private $dialect = null;
    
    /**
     * Constructor
     *
     * @param array $config
     * @return void
     */
    public function __construct($config = array())
    {
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * Adds a connection to the connection pool
     *
     * @param PDO $connection
     * @param string $connectionName
     * @return void
     */
    public function addConnection(PDO $connection, $connectionName = null)
    {
        if ($connectionName === null)
            $this->connectionPool[] = $connection;
        else
            $this->connectionPool[$connectionName] = $connection;
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
}

interface Stato_Dialect
{
    public function getDsn(array $params);
    
    public function getConnection(array $params);
    
    public function getTableNames(PDO $connection);
    
    public function hasTable(PDO $connection, $tableName);
    
    public function reflectTable(PDO $connection, $tableName);
    
    public function createTable(PDO $connection, Stato_Table $table);
    
    public function getColumnSpecification(Stato_Column $column);
    
    public function getDefaultValue(Stato_Column $column);
}