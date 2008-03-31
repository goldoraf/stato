<?php

class SInvalidStatementException extends Exception {}

interface SDbLibraryWrapper
{
    public function connect($host, $user, $pass, $dbname);
    public function disconnect();
    public function query($sql);
    public function last_insert_id();
    public function row_count($resource);
    public function free_result($resource);
    public function fetch($resource, $associative = true);
    public function supports_transactions();
    public function quote_string($str);
}

abstract class SAbstractAdapter
{
    protected $conn = null;
    protected $log  = array();
    protected $columns_cache   = array();
    protected $native_db_types = array();

    public $runtime = 0;
    public $config = array
    (
        'host'   => null,
        'port'   => null,
        'user'   => null,
        'pass'   => null,
        'dbname' => null
    );

    abstract public function connect();

    abstract public function disconnect();

    abstract public function execute($sql);

    abstract public function limit($count, $offset=0);

    abstract public function query_columns($table);

    abstract public function get_last_update($table);

    public function __construct($config = array())
    {
        $this->config = array_merge($this->config, $config);
        $this->connect();
    }

    public function select($sql)
    {
        return $this->execute($sql);
    }

    public function select_all($sql)
    {
        $rs = $this->select($sql);
        $set = array();
        while ($row = $this->fetch($rs)) $set[] = $row;
        $this->free_result($rs);
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
        return $this->execute($sql);
    }

    public function columns($table)
    {
        if (!isset($this->columns_cache[$table]))
            $this->columns_cache[$table] = $this->query_columns($table);

        return $this->columns_cache[$table];
    }

    public function reset_columns_cache()
    {
        $this->columns_cache = array();
    }

    public function last_insert_id()
    {
        return $this->conn->last_insert_id();
    }

    public function row_count($resource)
    {
        return $this->conn->row_count($resource);
    }

    public function free_result($resource)
    {
        return $this->conn->free_result($resource);
    }

    public function fetch($resource, $associative = true)
    {
        return $this->conn->fetch($resource, $associative);
    }

    public function supports_transactions()
    {
        return $this->conn->supports_transactions();
    }

    public function begin_transaction()
    {
        return $this->conn->begin_transaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

    public function rollback()
    {
        return $this->conn->rollback();
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
        elseif (preg_match('/datetime/i', $sql_type))
            return SColumn::DATETIME;
        elseif (preg_match('/timestamp/i', $sql_type))
            return SColumn::TIMESTAMP;
        elseif (preg_match('/date/i', $sql_type))
            return SColumn::DATE;
        elseif (preg_match('/float|double|decimal|numeric/i', $sql_type))
            return SColumn::FLOAT;
    }

    // This method should be overriden when using PDO because PDO supports natively variables
    // binding in statements. Unfortunately, there was a bug in PDO until PHP 5.2.4 that
    // causes problems with placeholders and escaped quotes (http://bugs.php.net/bug.php?id=42113)
    // In order to not force users to use PHP 5.2.4+, PDO variables binding is not used.
    public function sanitize_sql($stmt, $params = array())
    {
        if (!empty($params))
        {
            if (strpos($stmt, ':')) return $this->replace_named_bind_variables($stmt, $params);
            elseif (strpos($stmt, '?')) return $this->replace_bind_variables($stmt, $params);
            else return vsprintf($stmt, $params);
        }
        return $stmt;
    }

    public function replace_bind_variables($stmt, $params)
    {
        foreach ($params as $value) $stmt = preg_replace('/\?/i', $this->quote($value), $stmt, 1);
        return $stmt;
    }

    public function replace_named_bind_variables($stmt, $params)
    {
        foreach ($params as $key => $value)
        {
            if (strpos($key, ':') === false) $key = ':'.$key;
            $stmt = preg_replace('/'.$key.'/i', $this->quote($value), $stmt/*, 1*/);
        }
        return $stmt;
    }

    public function quote($value, $attribute_type = Null)
    {
        if ($value === Null) return "NULL";
        if (is_object($value)) return $this->conn->quote_string($value->__toString());
        switch($attribute_type)
        {
            case SColumn::DATE:
                return $this->conn->quote_string($value->__toString());
                break;
            case SColumn::DATETIME:
                return $this->conn->quote_string($value->__toString());
                break;
            case SColumn::BOOLEAN:
                return ($value === True ? '1' : '0');
                break;
            default:
                return $this->conn->quote_string($value);
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

    public function get_log()
    {
        return $this->log;
    }

    protected function log($sql, $time, $name = null)
    {
        $this->log[] = (($name === null) ? 'SQL' : $name)
                       ." (".sprintf("%.5f", $time).")\n    $sql";
    }
}

?>
