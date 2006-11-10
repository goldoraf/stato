<?php

class SInvalidStatementException extends Exception {}

abstract class SAbstractDriver
{
    private $conn = null;
    private $log  = array();
    
    protected $nativeDbTypes   = array();
    protected $simplifiedTypes = array();
    
    public $runtime = 0;
    public $config = array
    (
        'host'   => null,
        'port'   => null,
        'user'   => null,
        'pass'   => null,
        'name'   => null
    );
    
    public function __construct($config = array())
    {
        $this->config = array_merge($this->config, $config);
    }
    
    public function select($sql)
    {
        return $this->execute($sql);
    }
    
    public function selectAll($sql)
    {
        $rs = $this->select($sql);
        $set = array();
        while($row = $this->fetch($rs)) $set[] = $row;
        $this->free($rs);
        return $set;
    }
    
    public function selectOne($sql)
    {
        return $this->fetch($this->select($sql));
    }
    
    public function insert($sql)
    {
        if (!$this->execute($sql)) return false;
        return $this->lastInsertId();
    }
    
    public function update($sql)
    {
        if (!$this->execute($sql)) return false;
        return $this->affectedRows();
    }
    
    public function extractLength($sqlType)
    {
        preg_match('/\((.*)\)/', $sqlType, $matches);
        if (!empty($matches)) return $matches[1];
        return false;
    }
    
    public function simplifiedType($sqlType)
    {
        foreach($this->simplifiedTypes as $regex => $type)
            if (preg_match($regex, $sqlType)) return $type;
    }
    
    public function quote($value, $attributeType = Null)
    {
        if ($value === Null) return "NULL";
        if (is_object($value)) return $this->quoteString($value->__toString());
        switch($attributeType)
        {
            case 'date':
                return $this->quoteString($value->__toString());
                break;
            case 'datetime':
                return $this->quoteString($value->__toString());
                break;
            case 'boolean':
                return ($value === True ? '1' : '0');
                break;
            default:
                return $this->quoteString($value);
        }
    }
    
    public function quoteColumnName($name)
    {
        return $name;
    }
    
    public function arrayQuote($array)
    {
        foreach($array as $key => $value) $array[$key] = $this->quote($value);
        return $array;
    }
    
    public function writeLog()
    {
        $logger = SLogger::getInstance();
        foreach ($this->log as $log) $logger->debug($log);
    }
    
    protected function quoteString($value)
    {
        return "'".$this->escapeStr($value)."'";
    }
    
    protected function log($sql, $time, $name = null)
    {
        $this->log[] = (($name === null) ? 'SQL' : $name)
                       ." (".sprintf("%.5f", $time).")\n    $sql";
    }
    
    abstract public function connect();
    
    abstract public function disconnect();
    
    abstract public function getError();
    
    abstract public function execute($sql);
    
    abstract public function limit($count, $offset=0);
    
    abstract public function columns($table);
    
    abstract public function lastInsertId();
    
    abstract public function affectedRows();
    
    abstract public function rowCount($resource);
    
    abstract public function fetch($resource, $associative = true);
    
    abstract public function escapeStr($str);
}

?>
