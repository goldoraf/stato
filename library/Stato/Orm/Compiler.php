<?php

namespace Stato\Orm;

class UnknownClauseElementType extends Exception {}

class UnknownOperator extends Exception {}

class Compiler
{
    protected static $visitables = array(
        'TableClause', 'Table', 'Alias', 'Insert', 'Select', 'Expression', 'UnaryExpression', 'ExpressionList', 
        'Grouping', 'ClauseList', 'ClauseColumn', 'Column', 'BindParam', 'NullElement', 'Join'
    );
    
    protected static $operators = array(
        Operators::EQ => '=',
        Operators::NE => '!=',
        Operators::IN => 'IN',
        Operators::IS => 'IS',
        Operators::ISNOT => 'IS NOT',
        Operators::LT => '<',
        Operators::LE => '<=',
        Operators::GT => '>',
        Operators::GE => '>=',
        Operators::LIKE => 'LIKE',
        Operators::NOTLIKE => 'NOT LIKE',
        Operators::AND_ => 'AND',
        Operators::OR_ => 'OR',
        Operators::NOT_ => 'NOT',
        Operators::ASC => 'ASC',
        Operators::DESC => 'DESC',
    );
    
    protected $preparer;
    
    protected $bindParams;
    
    protected $bindCounter;
    
    public function __construct()
    {
        $this->preparer = new IdentifierPreparer();
        $this->bindParams = array();
        $this->bindCounter = array();
    }
    
    public function compile(ClauseElement $elmt)
    {
        $sqlString = $this->process($elmt);
        return new Compiled($sqlString, $this->bindParams);
    }
    
    protected function process(ClauseElement $elmt)
    {
        if (!preg_match('/^([a-zA-Z_]*)$/', str_replace(__NAMESPACE__.'\\', '', get_class($elmt)), $m) || !in_array($m[1], self::$visitables))
            throw new UnknownClauseElementType(get_class($elmt));
        
        $visitMethod = 'visit'.$m[1];
        return $this->$visitMethod($elmt);
    }
    
    protected function visitInsert(Insert $insert)
    {
        $colParams = $this->getColumnParams($insert);
        return 'INSERT INTO '.$insert->table->getName().' ('
            .implode(', ', array_keys($colParams)).') VALUES ('
            .implode(', ', array_values($colParams)).')';
    }
    
    protected function visitSelect(Select $select)
    {
        $columns = array();
        foreach ($select->getColumns() as $c) {
            $columns[] = $this->process($c);
        }
        $froms = array();
        foreach ($select->getFroms() as $f) {
            $froms[] = $this->process($f);
        }
        $sql = 'SELECT '.(($select->distinct) ? 'DISTINCT ' : '').implode(', ', $columns)
             .' FROM '.implode(', ', $froms);
        $where = $select->whereClause;
        $orderBy = $select->orderByClause;
        $limit = $this->getLimitClause($select->offset, $select->limit);
        if ($where !== null) $sql.= ' WHERE '.$this->process($where);
        if ($orderBy !== null) $sql.= ' ORDER BY '.$this->process($orderBy);
        if (!empty($limit)) $sql.= $limit;
        return $sql;
    }
    
    protected function visitTableClause(TableClause $table)
    {
        return $this->preparer->formatTable($table->getName());
    }
    
    protected function visitTable(Table $table)
    {
        return $this->preparer->formatTable($table->getName());
    }
    
    protected function visitAlias(Alias $alias)
    {
        return $this->process($alias->table).' AS '.$alias->alias;
    }
    
    protected function visitJoin(Join $join)
    {
        return $this->process($join->left).(($join->isOuter) ? ' LEFT OUTER JOIN ' : ' JOIN ')
        .$this->process($join->right).' ON '.$this->process($join->onClause);
    }
    
    protected function visitExpression(Expression $clause)
    {
        $op = $this->getOperatorString($clause->op);
        return $this->process($clause->left).' '.$op.' '.$this->process($clause->right);
    }
    
    protected function visitUnaryExpression(UnaryExpression $exp)
    {
        $str = $this->process($exp->element);
        if ($exp->operator)
            $str = $this->getOperatorString($exp->operator).' '.$str;
        if ($exp->modifier)
            $str = $str.' '.$this->getOperatorString($exp->modifier);
        return $str;
    }
    
    protected function visitExpressionList(ExpressionList $list)
    {
        $exps = array();
        $op = $this->getOperatorString($list->operator);
        foreach ($list->expressions as $exp) {
            if ($exp instanceof ExpressionList) $exp = new Grouping($exp);
            $exps[] = $this->process($exp);
        }
        return implode(' '.$op.' ', $exps);
    }
    
    protected function visitGrouping(Grouping $grouping)
    {
        return '('.$this->process($grouping->element).')';
    }
    
    protected function visitClauseList(ClauseList $list)
    {
        $elements = array();
        foreach ($list->clauses as $elt) $elements[] = $this->process($elt);
        return implode($list->separator, $elements);
    }
    
    protected function visitClauseColumn(ClauseColumn $column)
    {
        return $this->preparer->formatColumn($column);
    }
    
    protected function visitColumn(Column $column)
    {
        return $this->preparer->formatColumn($column);
    }
    
    protected function visitBindParam(BindParam $param)
    {
        if (!array_key_exists($param->key, $this->bindCounter))
            $this->bindCounter[$param->key] = 1;
        else
            $this->bindCounter[$param->key]++;
            
        $key = $param->key.'_'.$this->bindCounter[$param->key];
        $this->bindParams[$key] = $param->value;
        return $key;
    }
    
    protected function visitNullElement(NullElement $null)
    {
        return 'NULL';
    }
    
    protected function getLimitClause($offset, $limit)
    {
        $str = '';
        if ($limit !== null) $str.= " LIMIT $limit";
        if ($offset !== null) {
            if ($limit === null) $str.= " LIMIT -1";
            $str.= " OFFSET $offset";
        }
        return $str;
    }
    
    protected function getColumnParams($insert)
    {
        $colParams = array();
        if ($insert->values === null) {
            $params = array_keys($insert->table->getColumns());
            foreach ($params as $p) $colParams[$p] = ":{$p}";
        } else {
            $params = array_keys($insert->values);
            $diff = array_diff($params, array_keys($insert->table->getColumns()));
            
            if (!empty($diff))
                throw new UnknownColumn(implode(', ', $diff).' in '.$insert->table->getName().' table');
            
            foreach ($params as $p) {
                $bindName = ":{$p}";
                $colParams[$p] = $bindName;
                $this->bindParams[$bindName] = $insert->values[$p];
            }
        }
        return $colParams;
    }
    
    public function getOperatorString($op)
    {
        if (!array_key_exists($op, self::$operators)) return $op;
        return self::$operators[$op];
    }
}

class IdentifierPreparer
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
    
    public function formatColumn(ClauseColumn $column)
    {
        if ($column->table === null) return $this->quoteIdentifier($column->name);
        else return $this->formatTable($column->table->getName()).'.'.$this->quoteIdentifier($column->name);
    }
}

class Compiled
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