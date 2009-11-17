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
     * Returns an associative array
     */
    const FETCH_ASSOC = 1;
    
    /**
     * Returns a new instance of the provided class
     */
    const FETCH_OBJECT = 2;
    
    /**
     * Returns a new entity instance
     */
    const FETCH_ENTITY = 3;
    
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
    
    /**
     * Specific DB name
     *
     * @var string
     */
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
    
    public function execute($stmt, array $params = array())
    {
        if (!is_string($stmt)) {
            if (!$stmt instanceof Statement)
                throw new Exception("Can't execute instances of ".get_class($stmt));
            
            $stmt = $stmt->compile();
            $params = $stmt->params;
        }
        
        if (empty($params)) {
            $pdoStmt = $this->connection->query($stmt);
        } else {
            $pdoStmt = $this->connection->prepare($stmt);
            $pdoStmt->execute($params);
        }
        
        return new ResultProxy($this->connection, $pdoStmt);
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

class ResultProxy
{
    private $connection;
    private $stmt;
    
    public function __construct(\PDO $connection, \PDOStatement $stmt)
    {
        $this->connection = $connection;
        $this->stmt = $stmt;
    }
    
    public function setFetchMode($mode, $arg = null)
    {
        switch ($mode) {
            case Connection::FETCH_ASSOC:
                $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
                break;
            case Connection::FETCH_OBJECT:
                $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $arg);
                break;
            case Connection::FETCH_ENTITY:
                $this->stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $arg);
                break;
        }
    }
    
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    
    public function close()
    {
        $this->stmt->closeCursor();
    }
    
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }
    
    public function fetch()
    {
        return $this->stmt->fetch();
    }
}
