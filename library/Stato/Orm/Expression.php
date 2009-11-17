<?php

namespace Stato\Orm;

class UnknownColumn extends Exception {}

class JoinConditionError extends Exception {}

class Operators
{
    const EQ = 'eq';
    const NE = 'ne';
    const IN = 'in';
    const IS = 'is';
    const ISNOT = 'isnot';
    const LT = 'lt';
    const LE = 'le';
    const GT = 'gt';
    const GE = 'ge';
    const LIKE = 'like';
    const NOTLIKE = 'notlike';
    const AND_ = 'and';
    const OR_ = 'or';
    const NOT_ = 'not';
    const ASC = 'asc';
    const DESC = 'desc';
    
    private static $opInverses = array(
        self::EQ => self::NE,
        self::NE => self::EQ,
        self::IS => self::ISNOT,
        self::ISNOT => self::IS,
        self::LT => self::GE,
        self::LE => self::GT,
        self::GT => self::LE,
        self::GE => self::LT,
        self::LIKE => self::NOTLIKE,
        self::NOTLIKE => self::LIKE
    );
    
    public static function negate($op)
    {
        if (!array_key_exists($op, self::$opInverses)) return false;
        return self::$opInverses[$op];
    }
}

abstract class ClauseElement
{
    public function __toString()
    {
        return $this->compile()->__toString();
    }
    
    public function compile()
    {
        $compiler = new Compiler();
        return $compiler->compile($this);
    }
    
    public function negate()
    {
        throw new \Exception('Not implemented');
    }
}

abstract class Statement extends ClauseElement
{
    
}

/**
 * Describes a list of clauses, separated by an operator.
 * 
 * Defaults to comma-separated list.
 */
class ClauseList extends ClauseElement
{
    public $clauses;
    public $separator;
    
    public function __construct(array $clauses = array(), $separator = ',')
    {
        $this->clauses = $clauses;
        $this->separator = $separator;
    }
    
    public function append(ClauseElement $elt)
    {
        $this->clauses[] = $elt;
    }
}

class TableClause extends ClauseElement
{
    protected $name;
    protected $columns;
    
    public function __construct($name, array $columns)
    {
        $this->name = $name;
        $this->columns = array();
        foreach ($columns as $column) $this->addColumn($column);
    }
    
    public function __get($columnName)
    {
        if (!array_key_exists($columnName, $this->columns))
            throw new UnknownColumn("{$columnName} in {$this->name} table");
        
        return new ClauseColumn($columnName, $this);
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getColumns()
    {
        return $this->columns;
    }
    
    public function addColumn(Column $column)
    {
        $this->columns[$column->name] = $column;
    }
    
    public function getClauseColumns($columns = null)
    {
        $clauses = array();
        if ($columns === null) $columns = array_keys($this->columns);
        foreach ($columns as $column) {
            if ($column instanceof ClauseColumn)
                $clauses[] = $column;
            else
                $clauses[] = new ClauseColumn($column, $this);
        }
        return $clauses;
    }
    
    public function insert(array $values = null)
    {
        return new Insert($this, $values);
    }
    
    public function update(array $values = null, $whereClause = null)
    {
        return new Update($this, $values, $whereClause);
    }
    
    public function delete($whereClause = null)
    {
        return new Delete($this, $whereClause);
    }
    
    public function select($columns = null, $whereClause = null)
    {
        return new Select($this->getClauseColumns($columns), $whereClause);
    }
    
    public function alias($as)
    {
        return new Alias($this, $as);
    }
    
    public function join(TableClause $right, Expression $onClause = null, $isOuter = false)
    {
        return new Join($this, $right, $onClause, $isOuter);
    }
    
    public function outerJoin(TableClause $right, Expression $onClause = null)
    {
        return $this->join($right, $onClause, true);
    }
}

class Alias extends TableClause
{
    public $table;
    public $columns;
    public $name;
    public $alias;
    
    public function __construct(TableClause $table, $alias)
    {
        $this->table = $table;
        $this->alias = $alias;
        $this->name = $alias;
        $this->columns = $table->columns;
    }
}

class Insert extends Statement
{
    public $table;
    public $values;
    
    public function __construct(Table $table, array $values = null)
    {
        $this->table = $table;
        $this->values = $values;
    }
    
    public function values(array $values)
    {
        $this->values = $values;
        return $this;
    }
}

abstract class UpdateBase extends Statement
{
    public $whereClause;
    
    public function where()
    {
        $expressions = func_get_args();
        $this->appendWhereClause($expressions);
        return $this;
    }
    
    private function appendWhereClause($expressions)
    {
        if ($this->whereClause === null) $this->whereClause = new ExpressionList();
        foreach ($expressions as $expression) {
            if (!$expression instanceof Expression && !$expression instanceof ExpressionList)
                throw new Exception('where() argument must be instance of Expression or ExpressionList');
            
            $this->whereClause->append($expression);
        }
    }
}

class Update extends UpdateBase
{
    public $table;
    public $values;
    
    public function __construct(Table $table, array $values = null, $whereClause = null)
    {
        $this->table = $table;
        $this->values = $values;
        $this->whereClause = $whereClause;
    }
    
    public function values(array $values)
    {
        $this->values = $values;
        return $this;
    }
}

class Delete extends UpdateBase
{
    public $table;
    
    public function __construct(Table $table, $whereClause = null)
    {
        $this->table = $table;
        $this->whereClause = $whereClause;
    }
}

class Select extends Statement
{
    public $whereClause;
    public $orderByClause;
    public $offset;
    public $limit;
    public $distinct;
    
    private $columns;
    private $froms;
    
    public function __construct(array $columns, $whereClause = null)
    {
        $this->froms = array();
        $this->columns = array();
        $this->offset = null;
        $this->limit = null;
        $this->whereClause = $whereClause;
        $this->orderByClause = null;
        
        foreach ($columns as $c) {
            if ($c instanceof ClauseColumn)
                $this->columns[] = $c;
            elseif ($c instanceof TableClause)
                $this->columns = array_merge($this->columns, $c->getClauseColumns());
        }
        
        foreach ($this->columns as $c) 
            if (!in_array($c->table, $this->froms)) $this->froms[] = $c->table;
    }
    
    public function getColumns()
    {
        return $this->columns;
    }
    
    public function getFroms()
    {
        return $this->froms;
    }
    
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }
    
    public function where()
    {
        $expressions = func_get_args();
        $this->appendWhereClause($expressions);
        return $this;
    }
    
    public function orderBy()
    {
        $clauses = func_get_args();
        $this->appendOrderByClause($clauses);
        return $this;
    }
    
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }
    
    private function appendWhereClause($expressions)
    {
        if ($this->whereClause === null) $this->whereClause = new ExpressionList();
        foreach ($expressions as $expression) {
            if (!$expression instanceof Expression && !$expression instanceof ExpressionList)
                throw new Exception('where() argument must be instance of Expression or ExpressionList');
            
            $this->whereClause->append($expression);
        }
    }
    
    private function appendOrderByClause($clauses)
    {
        if ($this->orderByClause === null) $this->orderByClause = new ClauseList();
        foreach ($clauses as $clause) $this->orderByClause->append($clause);
    }
}

class ClauseColumn extends ClauseElement
{
    public $name;
    public $table;
    
    public function __construct($name, $table = null)
    {
        $this->name = $name;
        $this->table = $table;
    }
    
    public function op($op, $other)
    {
        return $this->compare($op, $other);
    }
    
    public function eq($other)
    {
        return $this->compare(Operators::EQ, $other);
    }
    
    public function ne($other)
    {
        return $this->compare(Operators::NE, $other);
    }
    
    public function lt($other)
    {
        return $this->compare(Operators::LT, $other);
    }
    
    public function le($other)
    {
        return $this->compare(Operators::LE, $other);
    }
    
    public function gt($other)
    {
        return $this->compare(Operators::GT, $other);
    }
    
    public function ge($other)
    {
        return $this->compare(Operators::GE, $other);
    }
    
    public function like($other)
    {
        return $this->compare(Operators::LIKE, $other);
    }
    
    /**
     * Produces the clause LIKE '<$other>%'
     */
    public function startswith($other)
    {
        return $this->compare(Operators::LIKE, (string) $other.'%');
    }
    
    /**
     * Produces the clause LIKE '%<$other>'
     */
    public function endswith($other)
    {
        return $this->compare(Operators::LIKE, '%'.(string) $other);
    }
    
    /**
     * Produces the clause LIKE '%<$other>%'
     */
    public function contains($other)
    {
        return $this->compare(Operators::LIKE, '%'.(string) $other.'%');
    }
    
    /**
     * Produces the clause IN (...)
     */
    public function in($other)
    {
        $params = array();
        foreach ($other as $o) $params[] = $this->bindParam($o);
        $other = new Grouping(new ClauseList($params));
        return $this->compare(Operators::IN, $other);
    }
    
    public function asc()
    {
        return new UnaryExpression($this, false, Operators::ASC);
    }
    
    public function desc()
    {
        return new UnaryExpression($this, false, Operators::DESC);
    }
    
    protected function compare($op, $other)
    {
        if ($other === null)
            return new Expression($this, new NullElement, Operators::IS);
            
        $other = $this->checkLiteral($other);
        return new Expression($this, $other, $op);
    }
    
    protected function checkLiteral($other)
    {
        if (!$other instanceof ClauseElement)
            $other = $this->bindParam($other);
        return $other;
    }
    
    protected function bindParam($other)
    {
        return new BindParam(':'.$this->name, $other);
    }
}

class Expression extends ClauseElement
{
    public $left;
    public $right;
    public $op;
    
    public function __construct($left, $right, $op)
    {
        $this->left = $left;
        $this->right = $right;
        $this->op = $op;
    }
    
    public function negate()
    {
        $nop = Operators::negate($this->op);
        if (!$nop) return new UnaryExpression(new Grouping($this), Operators::NOT_);
        $this->op = $nop;
        return $this;
    }
}

class UnaryExpression extends ClauseElement
{
    public $element;
    public $operator;
    public $modifier;
    
    public function __construct($elt, $operator = false, $modifier = false)
    {
        $this->element = $elt;
        $this->operator = $operator;
        $this->modifier = $modifier;
    }
}

class ExpressionList extends ClauseElement
{
    public $expressions;
    public $operator;
    
    public function __construct(array $expressions = array(), $operator = Operators::AND_)
    {
        $this->expressions = $expressions;
        $this->operator = $operator;
    }
    
    public function append(ClauseElement $elt)
    {
        $this->expressions[] = $elt;
    }
}

class Grouping extends ClauseElement
{
    public $element;
    
    public function __construct($element)
    {
        $this->element = $element;
    }
}

class BindParam extends ClauseElement
{
    public $key;
    public $value;
    
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}

class NullElement extends ClauseElement
{
    
}

class Join extends ClauseElement
{
    public $left;
    public $right;
    public $onClause;
    public $isOuter;
    
    public function __construct(Table $left, Table $right, Expression $onClause = null, $isOuter = false)
    {
        $this->left = $left;
        $this->right = $right;
        $this->isOuter = $isOuter;
        
        if ($onClause === null)
            $onClause = $this->getJoinCondition($left, $right);
            
        $this->onClause = $onClause;
    }
    
    private function getJoinCondition(Table $a, Table $b)
    {
        $crit = array();
        foreach ($b->getForeignKeys() as $fk) {
            $col = $fk->getReferentColumn($a);
            if ($col) {
                $crit[] = $col->eq($fk->getParent());
            }
        }
        if ($a != $b) {
            foreach ($a->getForeignKeys() as $fk) {
                $col = $fk->getReferentColumn($b);
                if ($col) {
                    $crit[] = $col->eq($fk->getParent());
                }
            }
        }
        if (count($crit) == 0)
            throw new JoinConditionError("Can't find any foreign key relationships "
            ."between '".$a->getName()."' and '".$b->getName()."'");
        elseif (count($crit) > 1)
            return new ExpressionList($crit);
        
        return $crit[0];
    }
}