<?php

class SPdoMysqlLibraryWrapper implements SDbLibraryWrapper
{
    private $pdo;
    
    public function connect($host, $user, $pass, $dbname)
    {
        $this->pdo = new PDO($this->dsn($host, $dbname), $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $this->pdo->exec("SET NAMES 'utf8'");
        $this->pdo->exec("SET CHARACTER SET 'utf8'");
    }
    
    public function dsn($host, $dbname)
    {
        return "mysql:host={$host};dbname={$dbname}";
    }
    
    public function disconnect()
    {
        $this->pdo = null;
    }
    
    public function query($sql)
    {
        try {
            if (strpos($sql, 'SELECT') === 0 || strpos($sql, 'SHOW') === 0)
                return $this->pdo->query($sql); // returns a PDO statement
            else
                return $this->pdo->exec($sql); // returns affected rows
        } 
        catch (PDOException $e) {
            throw new SInvalidStatementException($e->getMessage()."\nSQL used : $sql");
        }
    }
    
    public function last_insert_id()
    {
        return $this->pdo->lastInsertId();
    }
    
    public function row_count($stmt)
    {
        return $stmt->rowCount();
    }
    
    public function free_result($stmt)
    {
        return $stmt->closeCursor();
    }
    
    public function fetch($stmt, $associative = true)
    {
        if ($associative) return $stmt->fetch(PDO::FETCH_ASSOC);
        else return $stmt->fetch(PDO::FETCH_NUM);
    }
    
    public function supports_transactions()
    {
        return true;
    }
    
    public function begin_transaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    public function commit()
    {
        return $this->pdo->commit();
    }
    
    public function rollback()
    {
        return $this->pdo->rollBack();
    }
    
    public function quote_string($str)
    {
        return "'$str'";
    }
}

?>
