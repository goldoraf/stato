<?php

class Stato_QueryException extends Exception {}

class Stato_RecordNotFound extends Exception {}

class Stato_Query /*implements Iterator, Countable*/
{
    private $entity;
    private $table;
    private $connection;
    private $criterion;
    
    public function __construct($entity, Stato_Connection $connection)
    {
        $this->entity = $entity;
        $this->table = Stato_Mapper::getTable($this->entity);
        $this->connection = $connection;
        $this->criterion = new Stato_ExpressionList(array());
    }
    
    public function __clone()
    {
        $this->criterion = clone $this->criterion;
    }
    
    public function all()
    {
        $stmt = $this->connection->execute($this->getStatement());
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        return $stmt->fetchAll();
    }
    
    public function get($id)
    {
        $pk = $this->table->getPrimaryKeyColumn();
        $select = $this->table->select()->where($pk->eq($id));
        $stmt = $this->connection->execute($select);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        $obj = $stmt->fetch();
        $stmt->closeCursor();
        if (!$obj)
            throw new Stato_RecordNotFound();
        return $obj;
    }
    
    public function inBulk($ids)
    {
        $pk = $this->table->getPrimaryKeyColumn();
        $select = $this->table->select()->where($pk->in($ids));
    }
    
    /**
     * Returns a new <var>Stato_Query</var> instance with the args ANDed to the existing set.   
     */
    public function filter()
    {
        $criteria = func_get_args();
        $clone = clone $this;
        foreach ($criteria as $c) $clone->appendCriterion($c);
        return $clone;
    }
    
    public function appendCriterion($criterion)
    {
        if (!is_string($criterion) && !$this->inheritsFrom($criterion, 'Stato_ClauseElement'))
            throw new Stato_QueryException('filter() arguments must be instances of Stato_ClauseElement or strings');
        
        $this->criterion->append($criterion);
    }
    
    protected function getStatement()
    {
        return $this->table->select()->where($this->criterion);
    }
    
    protected function inheritsFrom($a, $b)
    {
        if (!is_object($a)) return false;
        $ref = new ReflectionObject($a);
        return $ref->isSubclassOf(new ReflectionClass($b));
    }
}