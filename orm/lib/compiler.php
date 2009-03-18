<?php

class Stato_UnknownClauseElementType extends Exception {}

class Stato_UnknownOperator extends Exception {}

class Stato_DefaultCompiler
{
    protected static $visitables = array(
        'TableClause', 'Table', 'Alias', 'Insert', 'Select', 'Expression', 'UnaryExpression', 'ExpressionList', 
        'Grouping', 'ClauseColumn', 'BindParam', 'Null'
    );
    
    protected static $operators = array(
        Stato_Operators::EQ => '=',
        Stato_Operators::NE => '!=',
        Stato_Operators::IS => 'IS',
        Stato_Operators::ISNOT => 'IS NOT',
        Stato_Operators::LT => '<',
        Stato_Operators::LE => '<=',
        Stato_Operators::GT => '>',
        Stato_Operators::GE => '>=',
        Stato_Operators::LIKE => 'LIKE',
        Stato_Operators::NOTLIKE => 'NOT LIKE',
        Stato_Operators::AND_ => 'AND',
        Stato_Operators::OR_ => 'OR',
        Stato_Operators::NOT_ => 'NOT',
    );
    
    protected $preparer;
    
    public function __construct()
    {
        $this->preparer = new Stato_IdentifierPreparer();
    }
    
    public function compile(Stato_ClauseElement $elmt, &$bindParams = null)
    {
        if (!preg_match('/^Stato_([a-zA-Z_]*)$/', get_class($elmt), $m) || !in_array($m[1], self::$visitables))
            throw new Stato_UnknownClauseElementType(get_class($elmt));
        
        $visitMethod = 'visit'.$m[1];
        if ($bindParams === null) $bindParams = array('counter' => array(), 'params' => array());
        $sqlString = $this->$visitMethod($elmt, $bindParams);
        return new Stato_Compiled($sqlString, $bindParams['params']);
    }
    
    protected function visitInsert(Stato_Insert $insert, array &$bindParams)
    {
        $colParams = $this->getColumnParams($insert);
        return 'INSERT INTO '.$insert->table->name.' ('
            .implode(', ', array_keys($colParams)).') VALUES ('
            .implode(', ', array_values($colParams)).')';
    }
    
    protected function visitSelect(Stato_Select $select, array &$bindParams)
    {
        $columns = array();
        foreach ($select->getColumns() as $c) {
            $columns[] = $this->compile($c, $bindParams);
        }
        $froms = array();
        foreach ($select->getFroms() as $f) {
            $froms[] = $this->compile($f, $bindParams);
        }
        $sql = 'SELECT '.implode(', ', $columns)
             .' FROM '.implode(', ', $froms);
        return $sql;
    }
    
    protected function visitTableClause(Stato_TableClause $table, array &$bindParams)
    {
        return $this->preparer->formatTable($table->name);
    }
    
    protected function visitTable(Stato_Table $table, array &$bindParams)
    {
        return $this->preparer->formatTable($table->name);
    }
    
    protected function visitAlias(Stato_Alias $alias, array &$bindParams)
    {
        return $this->compile($alias->table, $bindParams).' AS '.$alias->alias;
    }
    
    protected function visitExpression(Stato_Expression $clause, array &$bindParams)
    {
        $op = $this->getOperatorString($clause->op);
        return $this->compile($clause->left, $bindParams).' '.$op.' '.$this->compile($clause->right, $bindParams);
    }
    
    protected function visitUnaryExpression(Stato_UnaryExpression $exp, array &$bindParams)
    {
        $str = $this->compile($exp->element, $bindParams);
        if ($exp->operator)
            $str = $this->getOperatorString($exp->operator).' '.$str;
        if ($exp->modifier)
            $str = $str.' '.$this->getOperatorString($exp->modifier);
        return $str;
    }
    
    protected function visitExpressionList(Stato_ExpressionList $list, array &$bindParams)
    {
        $exps = array();
        $op = $this->getOperatorString($list->operator);
        foreach ($list->expressions as $exp) {
            if ($exp instanceof Stato_ExpressionList) $exp = new Stato_Grouping($exp);
            $exps[] = $this->compile($exp, $bindParams);
        }
        return implode(' '.$op.' ', $exps);
    }
    
    protected function visitGrouping(Stato_Grouping $grouping, array &$bindParams)
    {
        return '('.$this->compile($grouping->element, $bindParams).')';
    }
    
    protected function visitClauseColumn(Stato_ClauseColumn $column, array &$bindParams)
    {
        return $this->preparer->formatColumn($column);
    }
    
    protected function visitBindParam(Stato_BindParam $param, array &$bindParams)
    {
        if (!array_key_exists($param->key, $bindParams['counter']))
            $bindParams['counter'][$param->key] = 1;
        else
            $bindParams['counter'][$param->key]++;
            
        $key = $param->key.'_'.$bindParams['counter'][$param->key];
        $bindParams['params'][$key] = $param->value;
        return $key;
    }
    
    protected function visitNull(Stato_Null $null, array &$bindParams)
    {
        return 'NULL';
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
    
    public function getOperatorString($op)
    {
        if (!array_key_exists($op, self::$operators)) return $op;
        return self::$operators[$op];
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
    
    public function formatColumn(Stato_ClauseColumn $column)
    {
        if ($column->table === null) return $this->quoteIdentifier($column->name);
        else return $this->formatTable($column->table->name).'.'.$this->quoteIdentifier($column->name);
    }
}

class Stato_Compiled
{
    public $string;
    public $params;
    
    public function __construct($string, $params)
    {
        $this->string = $string;
        $this->params = $params;
    }
    
    public function __toString()
    {
        return $this->string;
    }
}