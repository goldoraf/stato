<?php

namespace Stato\Orm;

use \PDO, \PDOStatement, \IteratorAggregate, \IteratorIterator, \DateTime;

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

class ConnectionException extends Exception
{
    public function __construct(array $info)
    {
        list($sqlstate, $code, $msg) = $info;
        parent::__construct("[SQLSTATE:$sqlstate] $msg", $code);
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
        $this->connection = new PDO($this->dialect->getDsn($this->config), 
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
    
    public function close()
    {
        $this->connection = null;
    }
    
    public function getPDOConnection()
    {
        return $this->connection;
    }
    
    public function getDialect()
    {
        return $this->dialect;
    }
    
    public function getDatabase()
    {
        return $this->config['dbname'];
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
            if ($pdoStmt === false) {
                throw new ConnectionException($this->connection->errorInfo());
            }
        } else {
            $pdoStmt = $this->connection->prepare($stmt);
            $this->bindValues($pdoStmt, $params);
            $result = $pdoStmt->execute();
            if ($result === false) {
                throw new ConnectionException($pdoStmt->errorInfo());
            }
        }
        
        return new ResultProxy($this->connection, $pdoStmt);
    }
    
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }
    
    public function commit()
    {
        $this->connection->commit();
    }
    
    public function rollback()
    {
        $this->connection->rollback();
    }
    
    public function getTableNames()
    {
        return $this->dialect->getTableNames($this);
    }
    
    public function hasTable($tableName)
    {
        return in_array($tableName, $this->getTableNames());
    }
    
    public function reflectTable($tableName)
    {
        return $this->dialect->reflectTable($this, $tableName);
    }
    
    public function createTable(Table $table)
    {
        return $this->execute($this->dialect->createTable($table));
    }
    
    public function truncateTable($table)
    {
        $tableName = (is_object($table)) ? $table->getName() : $table;
        return $this->execute($this->dialect->truncateTable($tableName));
    }
    
    public function dropTable($table)
    {
        $tableName = (is_object($table)) ? $table->getName() : $table;
        return $this->execute($this->dialect->dropTable($tableName));
    }
    
    public function createDatabase($dbName)
    {
        return $this->execute($this->dialect->createDatabase($dbName));
    }
    
    public function dropDatabase($dbName)
    {
        return $this->execute($this->dialect->dropDatabase($dbName));
    }
    
    private function bindValues(PDOStatement $stmt, array $params)
    {
        foreach ($params as $bind => $param) {
            $type = PDO::PARAM_STR;
            if ($param instanceof DateTime) {
                $param = $param->format('Y-m-d H:i:s');
            } elseif (is_bool($param)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_null($param)) {
                $type = PDO::PARAM_NULL;
            } elseif (is_int($param)) {
                $type = PDO::PARAM_INT;
            } else {
                $param = (string) $param;
            }
            
            if (!$stmt->bindValue($bind, $param, $type)) {
                throw new ConnectionException($stmt->errorInfo());
            }
        }
    }
}

class ResultProxy implements IteratorAggregate
{
    private $connection;
    private $stmt;
    
    public function __construct(PDO $connection, PDOStatement $stmt)
    {
        $this->connection = $connection;
        $this->stmt = $stmt;
        $this->setFetchMode(Connection::FETCH_ASSOC);
    }
    
    public function getIterator()
    {
        return new IteratorIterator($this->stmt);
    }
    
    public function setFetchMode($mode, $arg = null)
    {
        switch ($mode) {
            case Connection::FETCH_ASSOC:
                $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
                break;
            case Connection::FETCH_OBJECT:
                $this->stmt->setFetchMode(PDO::FETCH_CLASS, $arg);
                break;
            case Connection::FETCH_ENTITY:
                $this->stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $arg);
                break;
            default:
                (is_null($arg)) ? $this->stmt->setFetchMode($mode) : $this->stmt->setFetchMode($mode, $arg);
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
    
    public function affectedRows()
    {
        return $this->rowCount();
    }
    
    public function fetch()
    {
        return $this->stmt->fetch();
    }
    
    public function fetchAll()
    {
        return $this->stmt->fetchAll();
    }
}
