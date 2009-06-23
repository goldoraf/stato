<?php

namespace Stato\Orm;

require_once 'Schema.php';

class UnboundConnectionError extends Exception
{
    public function __construct($class)
    {
        parent::__construct("The {$class} is not bound to a Connection.  "
           ."Execution can not proceed without a database to execute "
           ."against.  Either execute with an explicit connection or "
           ."assign {$class} to enable implicit execution.");
    }
}

/**
 * Connects a PDO connection to a specific SQL dialect
 * 
 * @package Stato
 * @subpackage Orm
 */
class Connection
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
        $dialectClass = __NAMESPACE__ . "\Dialect\\{$this->driver}";
        $this->dialect = new $dialectClass();
        $this->connection = new \PDO($this->dialect->getDsn($this->config), 
                                     $this->config['user'], $this->config['password'],
                                     $this->dialect->getDriverOptions());
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
    
    public function execute($stmt)
    {
        if (!is_string($stmt)) {
            if (!$stmt instanceof Statement)
                throw new Exception("Can't execute instances of ".get_class($stmt));
            $stmt = $stmt->compile();
            if (empty($stmt->params)) return $this->connection->query($stmt);
            
            $params = $stmt->params;
            $stmt = $this->connection->prepare($stmt);
            $stmt->execute($params);
            return $stmt;
        }
        
        return $this->connection->exec($stmt);
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
    
    public function createTable(Table $table)
    {
        return $this->connection->exec($this->dialect->createTable($table));
    }
    
    public function dropTable($table)
    {
        $tableName = (is_object($table)) ? $table->getName() : $table;
        return $this->connection->exec($this->dialect->dropTable($tableName));
    }
}
