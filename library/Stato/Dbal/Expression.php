<?php

namespace Stato\Dbal;

use \Exception;

class UnknownColumn extends Exception {}

class ClauseTypeError extends Exception {}

class JoinConditionError extends Exception {}

class Operators
{
    const EQ = 'eq';
    const NE = 'ne';
    const IN = 'in';
    const NOTIN = 'notin';
    const IS = 'is';
    const ISNOT = 'isnot';
    const BETWEEN = 'between';
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
        self::IN => self::NOTIN,
        self::NOTIN => self::IN,
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
    public $froms = array();
    
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
    
    public function select($whereClause = null)
    {
        return new Select($this, $whereClause);
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

class ColumnWildcard extends ClauseElement
{
    public $table;
    
    public function __construct(TableClause $table = null)
    {
        $this->table = $table;
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
    public $froms;
    
    public function __construct($columns, $whereClause = null)
    {
        $this->froms = array();
        $this->columns = array();
        $this->offset = null;
        $this->limit = null;
        $this->whereClause = null;
        $this->orderByClause = null;
        
        if (!is_array($columns)) $columns = array($columns);
        
        foreach ($columns as $c) {
            if ($c instanceof ClauseColumn) {
                $this->columns[] = $c;
                if (!in_array($c->table, $this->froms)) $this->froms[] = $c->table;
            } elseif ($c instanceof TableClause) {
                if (!in_array($c, $this->froms)) $this->froms[] = $c;
            } else {
                throw new ClauseTypeError('Columns passed to Select constructor must be instances of ClauseColumn or TableClause');
            }
        }
        
        if (!is_null($whereClause)) $this->appendWhereClause($whereClause);
    }
    
    public function getColumns()
    {
        if (count($this->columns) == 0) {
            if (count($this->froms) == 1) return array(new ColumnWildcard());
            $columns = array();
            foreach ($this->froms as $from) {
                $columns[] = new ColumnWildcard($from);
            }
            return $columns;
        }
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
    
    public function from($froms)
    {
        if (!is_array($froms)) $froms = array($froms);
        foreach ($froms as $from) {
            if (!$from instanceof TableClause && !$from instanceof Join)
                throw new ClauseTypeError('from() arguments must be instances of TableClause or Join');
        }
        $this->froms = $froms;
        return $this;
    }
    
    public function where()
    {
        $clauses = func_get_args();
        if (count($clauses) == 1) 
            $whereClause = $clauses[0];
        else
            $whereClause = new ExpressionList($clauses);
        $this->appendWhereClause($whereClause);
        return $this;
    }
    
    public function orderBy()
    {
        $clauses = func_get_args();
        foreach ($clauses as $clause) $this->appendOrderByClause($clause);
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
    
    private function appendWhereClause($whereClause)
    {
        $this->updateFromClause($whereClause);
        
        if (!is_null($this->whereClause))
            $this->whereClause = and_($this->whereClause, $whereClause);
        else
            $this->whereClause = $whereClause;
    }
    
    private function appendOrderByClause($clause)
    {
        if (is_null($this->orderByClause)) $this->orderByClause = new ClauseList();
        $this->orderByClause->append($clause);
    }
    
    private function updateFromClause($expression)
    {
        $froms = $expression->getFroms();
        foreach ($froms as $from) {
            if (!$from instanceof TableClause && !$from instanceof Join)
                throw new ClauseTypeError('from() arguments must be instances of TableClause or Join');
                
            if (!in_array($from, $this->froms)) $this->froms[] = $from;
        }
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
    
    /**
     * Produces a BETWEEN clause, ie <column> BETWEEN <left> AND <right>
     */
    public function between($left, $right)
    {
        return new Expression($this, 
            new ExpressionList(array($this->checkLiteral($left), $this->checkLiteral($right))), 
            Operators::BETWEEN);
    }
    
    public function asc()
    {
        return new UnaryExpression($this, false, Operators::ASC);
    }
    
    public function desc()
    {
        return new UnaryExpression($this, false, Operators::DESC);
    }
    
    public function label($as)
    {
        return new Label($this->name, $as, $this->table);
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

class Label extends ClauseColumn
{
    public $as;
    
    public function __construct($name, $as, $table = null)
    {
        $this->name = $name;
        $this->as = $as;
        $this->table = $table;
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
    
    public function getFroms()
    {
        return array_merge($this->left->froms, $this->right->froms);
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
    
    public function getFroms()
    {
        return $this->element->getFroms();
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
    
    public function getFroms()
    {
        $froms = array();
        foreach ($this->expressions as $exp) $froms = array_merge($froms, $exp->getFroms());
        return $froms;
    }
    
    public function negate()
    {
        return new UnaryExpression(new Grouping($this), Operators::NOT_);
    }
}

class Grouping extends ClauseElement
{
    public $element;
    
    public function __construct($element)
    {
        $this->element = $element;
    }
    
    public function getFroms()
    {
        return $this->element->getFroms();
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

function select($columns, $whereClause = null)
{
    return new Select($columns, $whereClause);
}

function and_()
{
    $expressions = func_get_args();
    if (count($expressions) == 1) return $expressions[0];
    return new ExpressionList($expressions, Operators::AND_);
}

function or_()
{
    $expressions = func_get_args();
    if (count($expressions) == 1) return $expressions[0];
    return new ExpressionList($expressions, Operators::OR_);
}

function not_(ClauseElement $elt)
{
    return $elt->negate();
}