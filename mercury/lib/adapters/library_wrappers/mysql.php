<?php

class SMysqlLibraryWrapper implements SDbLibraryWrapper
{
    private $conn = null;
    
    public function connect($host, $user, $pass, $dbname)
    {
        $this->conn = @mysql_connect($host, $user, $pass);
        mysql_select_db($dbname);
        mysql_query("SET NAMES 'utf8'");
    }
    
    public function disconnect()
    {
        mysql_close($this->conn);
        $this->conn = null;
    }
    
    public function get_error()
    {
        return mysql_errno($this->conn) . ": " . mysql_error($this->conn). "\n";
    }
    
    public function query($sql)
    {
        $result = @mysql_query($sql, $this->conn);
        
        if (is_resource($result)) return $result;
        
        if (!$result)
            throw new SInvalidStatementException('MySQL Error : '.$this->get_error().' ; SQL used : '.$sql);
            
        return true;
    }
    
    public function execute($sql)
    {
        $result = @mysql_query($sql, $this->conn);
        
        if (!$result)
            throw new SInvalidStatementException('MySQL Error : '.$this->get_error().' ; SQL used : '.$sql);
            
        return mysql_affected_rows($this->conn);
    }
    
    public function last_insert_id()
    {
        return mysql_insert_id($this->conn);
    }
    
    public function row_count($resource)
    {
        return @mysql_num_rows($resource);
    }
    
    public function free_result($resource)
    {
        return @mysql_free_result($resource);
    }
    
    public function fetch($resource, $associative = true)
    {
        if ($associative) return @mysql_fetch_assoc($resource);
        else return @mysql_fetch_row($resource);
    }
    
    public function supports_transactions()
    {
        return false;
    }
    
    public function quote_string($str)
    {
        return "'".mysql_real_escape_string($str)."'";
    }
}

?>
