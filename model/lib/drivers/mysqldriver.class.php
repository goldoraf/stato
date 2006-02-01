<?php

/**
 * MySqlDriver
 * 
 * Classe de connexion MySQL
 * 
 * @package 
 * @author Raphaël Rougeron
 * @copyright Copyright (c) 2004
 * @version 0.1
 * @access public
 **/
class MySqlDriver extends AbstractDriver
{
    public $nativeDbTypes = array
    (
        'primary_key'   => 'int(11) DEFAULT NULL auto_increment PRIMARY KEY',
        'string'        => array('name' => 'varchar', 'limit' => 255),
        'text'          => array('name' => 'text'),
        'integer'       => array('name' => 'int', 'limit' => 11),
        'boolean'       => array('name' => 'tinyint', 'limit' => 1)
    );
    
    protected $simplifiedTypes = array
    (
        '/tinyint|smallint|mediumint|int|bigint/i'  => 'integer',
        '/tinytext|text|mediumtext|longtext/i'      => 'text',
        '/float|double|decimal/i'                   => 'string',
        '/varchar|char/i'                           => 'string',
        '/datetime|timestamp/i'                     => 'datetime',
        '/date/i'                                   => 'date',
        '/enum|set/i'                               => 'string'
    );

    /**
     * MySqlDriver::connect()
     * 
     * @return bool
     **/
    public function connect()
    {
        $this->conn = @mysql_connect($this->config['host'],
                                     $this->config['user'],
                                     $this->config['pass']);
        
        mysql_select_db($this->config['dbname']);
        
        mysql_query("SET NAMES 'utf8'");
    }
    
    /**
     * MySqlDriver::disconnect()
     * 
     * @return void
     **/
    public function disconnect()
    {
        mysql_close($this->conn);
        $this->conn = null;
    }
    
    /**
     * MySqlDriver::getError()
     * 
     * @return string
     **/
    public function getError()
    {
        return mysql_errno($this->conn) . ": " . mysql_error($this->conn). "\n";
    }

    /**
     * MySqlDriver::execute()
     * 
     * @param $strsql la requete SQL
     * @return mixed
     **/
    public function execute($strsql)
    {
        $result = @mysql_query($strsql,$this->conn);
        if (is_resource($result))
        {
            return new Recordset($result, get_class($this));
        }
        if (!$result)
        {
            throw new Exception('MySQL Error : '.$this->getError().' ; SQL used : '.$strsql);
        }
        return true;
    }
    
    public function getColumns($table)
    {
        $rs = $this->execute("SHOW COLUMNS FROM ".$table);
        if ($rs)
        {
            $fields = array();
            while($row = $rs->fetch())
            {
                $fields[$row['Field']] = new Attribute($row['Field'], $this->simplifiedType($row['Type']), $row['Default']);
            }
            return $fields;
        }
        return false;
    }
    
    public function simplifiedType($sqlType)
    {
        if ($sqlType == 'tinyint(1)') return 'boolean';
        return parent::simplifiedType($sqlType);
    }
    
    /**
     * MySqlDriver::lastInsertId()
     * 
     * @return int
     **/
    public function lastInsertId()
    {
        return mysql_insert_id($this->conn);
    }
    
    /**
     * MySqlDriver::affectedRows()
     * 
     * @return int
     **/
    public function affectedRows()
    {
        return mysql_affected_rows($this->conn);
    }
    
    public static function rowCount($resource)
    {
        return @mysql_num_rows($resource);
    }
    
    public static function fetch($resource)
    {
        return @mysql_fetch_assoc($resource);
    }
    
    /**
     * MySqlDriver::getLastUpdate()
     * 
     * @param $table
     * @return mixed
     **/
    public function getLastUpdate($table)
    {
        $rs = $this->execute("SHOW TABLE STATUS LIKE '".$table."'");
        if (!$this->isError($rs))
        {
            $status = $rs->fetch();
            return $status['Update_time'];
        }
        return false;
    }
    
    /**
     * MySqlDriver::limit()
     * 
     * @return string
     **/
    public function limit($count, $offset=0)
    {
        if ($count > 0)
        {
            $sql = " LIMIT $count";
            if ($offset > 0)
            {
                $sql .= " OFFSET $offset";
            }
        }
        return $sql;
    }
    
    /**
     * MySqlDriver::escapeStr()
     * 
     * @param $str la chaine a escaper
     * @return string
     **/
    public function escapeStr($str)
    {
        // throw exception if magic_quotes ?
        return mysql_real_escape_string($str, $this->conn);
    }
    
    public function quoteColumnName($name)
    {
        return "`$name`";
    }
}

?>
