<?php

class SInvalidStatementException extends Exception {}

abstract class SAbstractAdapter
{
    private $conn = null;
    private $log  = array();
    
    protected $native_db_types = array();
    
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
    
    public function select_all($sql)
    {
        $rs = $this->select($sql);
        $set = array();
        while($row = $this->fetch($rs)) $set[] = $row;
        $this->free($rs);
        return $set;
    }
    
    public function select_one($sql)
    {
        return $this->fetch($this->select($sql));
    }
    
    public function insert($sql)
    {
        if (!$this->execute($sql)) return false;
        return $this->last_insert_id();
    }
    
    public function update($sql)
    {
        if (!$this->execute($sql)) return false;
        return $this->affected_rows();
    }
    
    public function extract_length($sql_type)
    {
        preg_match('/\((.*)\)/', $sql_type, $matches);
        if (!empty($matches)) return $matches[1];
        return false;
    }
    
    public function simplified_type($sql_type)
    {
        if (preg_match('/int/i', $sql_type))
            return SColumn::INTEGER;
        elseif (preg_match('/text/i', $sql_type))
            return SColumn::TEXT;
        elseif (preg_match('/char|string/i', $sql_type))
            return SColumn::STRING;
        elseif (preg_match('/boolean/i', $sql_type))
            return SColumn::BOOLEAN;
        elseif (preg_match('/datetime|timestamp/i', $sql_type))
            return SColumn::DATETIME;
        elseif (preg_match('/date/i', $sql_type))
            return SColumn::DATE;
        elseif (preg_match('/float|double|decimal|numeric/i', $sql_type))
            return SColumn::FLOAT;
    }
    
    public function quote($value, $attribute_type = Null)
    {
        if ($value === Null) return "NULL";
        if (is_object($value)) return $this->quote_string($value->__toString());
        switch($attribute_type)
        {
            case SColumn::DATE:
                return $this->quote_string($value->__toString());
                break;
            case SColumn::DATETIME:
                return $this->quote_string($value->__toString());
                break;
            case SColumn::BOOLEAN:
                return ($value === True ? '1' : '0');
                break;
            default:
                return $this->quote_string($value);
        }
    }
    
    public function quote_column_name($name)
    {
        return $name;
    }
    
    public function array_quote($array)
    {
        foreach($array as $key => $value) $array[$key] = $this->quote($value);
        return $array;
    }
    
    public function write_log()
    {
        $logger = SLogger::get_instance();
        foreach ($this->log as $log) $logger->debug($log);
    }
    
    protected function quote_string($value)
    {
        return "'".$this->escape_str($value)."'";
    }
    
    protected function log($sql, $time, $name = null)
    {
        $this->log[] = (($name === null) ? 'SQL' : $name)
                       ." (".sprintf("%.5f", $time).")\n    $sql";
    }
    
    abstract public function connect();
    
    abstract public function disconnect();
    
    abstract public function get_error();
    
    abstract public function execute($sql);
    
    abstract public function limit($count, $offset=0);
    
    abstract public function columns($table);
    
    abstract public function last_insert_id();
    
    abstract public function affected_rows();
    
    abstract public function row_count($resource);
    
    abstract public function fetch($resource, $associative = true);
    
    abstract public function escape_str($str);
}

?>
