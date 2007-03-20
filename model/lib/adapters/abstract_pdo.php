<?php

abstract class SAbstractPDOAdapter extends PDO
{
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
        parent::__construct($this->dsn(), $this->config['user'], $this->config['pass']);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function execute($sql, $name = null)
    {
        $start = microtime(true);
        
        try {
            $affected_rows = $this->exec($sql);
        } catch (PDOException $e) {
            throw new SInvalidStatementException($e->getMessage());
        }
        
        $time = microtime(true) - $start;
        $this->log($sql, $time, $name);
        $this->runtime += $time;
            
        return $affected_rows;
    }
    
    public function select($sql)
    {
        $start = microtime(true);
        
        try {
            $stmt = $this->query($sql);
        } catch (PDOException $e) {
            throw new SInvalidStatementException($e->getMessage());
        }
        
        $time = microtime(true) - $start;
        $this->log($sql, $time, $name);
        $this->runtime += $time;
            
        return $stmt;
    }
    
    public function select_all($sql)
    {
        return $this->select($sql)->fetchAll();
    }
    
    public function select_one($sql)
    {
        return $this->select($sql)->fetch();
    }
    
    public function insert($sql)
    {
        $this->execute($sql);
        return $this->lastInsertId();
    }
    
    public function update($sql)
    {
        return $this->execute($sql);
    }
    
    public function row_count($stmt)
    {
        return $stmt->rowCount();
    }
    
    public function fetch($stmt, $associative = true)
    {
        if ($associative) return $stmt->fetch(PDO::FETCH_ASSOC);
        else return $stmt->fetch(PDO::FETCH_NUM);
    }
    
    public function free_result($stmt)
    {
        return $stmt->closeCursor();
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
        if (is_object($value)) return parent::quote($value->__toString());
        switch($attribute_type)
        {
            case SColumn::DATE:
                return parent::quote($value->__toString());
                break;
            case SColumn::DATETIME:
                return parent::quote($value->__toString());
                break;
            case SColumn::BOOLEAN:
                return ($value === True ? '1' : '0');
                break;
            default:
                return parent::quote($value);
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
    
    protected function log($sql, $time, $name = null)
    {
        $this->log[] = (($name === null) ? 'SQL' : $name)
                       ." (".sprintf("%.5f", $time).")\n    $sql";
    }
    
    public function get_log()
    {
        return $this->log;
    }
    
    abstract public function dsn();
    
    abstract public function limit($count, $offset=0);
    
    abstract public function columns($table);
    
    abstract public function get_last_update($table);
}

?>
