<?php

class Stato_UnknownStatementType extends Exception {}

class Stato_DefaultCompiler
{
    private $preparer;
    
    public function __construct()
    {
        $this->preparer = new Stato_IdentifierPreparer();
    }
    
    public function compile(Stato_Statement $stmt)
    {
        switch (get_class($stmt)) {
            case 'Stato_Insert':
                return $this->visitInsert($stmt);
            case 'Stato_Select':
                return $this->visitSelect($stmt);
            default:
                throw new Stato_UnknownStatementType(get_class($stmt));
        }
    }
    
    protected function visitInsert(Stato_Insert $insert)
    {
        $colParams = $this->getColumnParams($insert);
        return 'INSERT INTO '.$insert->table->name.' ('
            .implode(', ', array_keys($colParams)).') VALUES ('
            .implode(', ', array_values($colParams)).')';
    }
    
    protected function visitSelect(Stato_Select $select)
    {
        $columns = array();
        foreach ($select->getColumns() as $c) {
            $columns[] = $this->preparer->formatColumn($c);
        }
        $froms = array();
        foreach ($select->getFroms() as $f) {
            $froms[] = $this->preparer->formatTable($f);
        }
        $sql = 'SELECT '.implode(', ', $columns)
             .' FROM '.implode(', ', $froms);
        return $sql;
    }
    
    protected function getColumnParams($insert)
    {
        $colParams = array();
        if ($insert->values !== null) {
            $params = array_keys($insert->values);
            $diff = array_diff($params, array_keys($insert->table->columns));
            if (!empty($diff))
                throw new Stato_UnknownColumn(implode(', ', $diff)." in {$insert->table->name} table");
        } else {
            $params = array_keys($insert->table->columns);
        }
        foreach ($params as $p) $colParams[$p] = ":{$p}";
        return $colParams;
    }
}

class Stato_IdentifierPreparer
{
    private $initialQuote;
    
    private $finalQuote;
    
    public function __construct($initialQuote = '', $finalQuote = null)
    {
        $this->initialQuote = $initialQuote;
        $this->finalQuote = ($finalQuote !== null) ? $finalQuote : $initialQuote;
    }
    
    public function quoteIdentifier($value)
    {
        return $this->initialQuote.$value.$this->finalQuote;
    }
    
    public function formatTable($table)
    {
        return $this->quoteIdentifier($table);
    }
    
    public function formatColumn(Stato_ColumnClause $column)
    {
        if ($column->table === null) return $this->quoteIdentifier($column->name);
        else return $this->formatTable($column->table).'.'.$this->quoteIdentifier($column->name);
    }
}