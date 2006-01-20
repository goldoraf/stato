<?php

abstract class AbstractDriver
{
    private $conn = null;
    
    protected $nativeDbTypes   = array();
    protected $simplifiedTypes = array();
    
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
        if (!$rs) return false;
        $set = array();
        while($row = $rs->fetch()) $set[] = $row;
        return $set;
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
        {
            if (preg_match($regex, $sqlType))
            {
                return $type;
            }
        }
    }
    
    public function quote($value, $attributeType = Null)
    {
        if ($value == Null) return "NULL";
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
    
    protected function quoteString($value)
    {
        return "'".$this->escapeStr($value)."'";
    }
    
    abstract protected function connect();
    
    abstract protected function disconnect();
    
    abstract protected function getError();
    
    abstract protected function execute($sql);
    
    abstract protected function limit($count, $offset=0);
    
    abstract protected function getColumns($table);
    
    abstract protected function lastInsertId();
    
    abstract protected function affectedRows();
    
    abstract protected static function rowCount($resource);
    
    abstract protected static function fetch($resource);
    
    abstract protected function escapeStr($str);
}

?>
