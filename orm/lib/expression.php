<?php

class Stato_UnknownColumn extends Exception {}

class Stato_Operators
{
    const EQ = 'eq';
    const NE = 'ne';
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

abstract class Stato_ClauseElement
{
    public function __toString()
    {
        return $this->compile()->__toString();
    }
    
    public function compile()
    {
        $compiler = new Stato_DefaultCompiler();
        return $compiler->compile($this);
    }
    
    public function negate()
    {
        throw new Exception('Not implemented');
    }
}

class Stato_TableClause extends Stato_ClauseElement
{
    public $name;
    public $columns;
    
    public function __construct($name, $columns = null)
    {
        $this->name = $name;
        $this->columns = array();
        foreach ($columns as $column) $this->addColumn($column);
    }
    
    public function __get($columnName)
    {
        if (!array_key_exists($columnName, $this->columns))
            throw new Stato_UnknownColumn("{$columnName} in {$this->table} table");
        
        return new Stato_ClauseColumn($columnName, $this);
    }
    
    public function addColumn(Stato_Column $column)
    {
        $this->columns[$column->name] = $column;
    }
    
    public function getClauseColumns($columns = null)
    {
        $clauses = array();
        if ($columns === null) $columns = array_keys($this->columns);
        foreach ($columns as $column) {
            if ($column instanceof Stato_ClauseColumn)
                $clauses[] = $column;
            else
                $clauses[] = new Stato_ClauseColumn($column, $this);
        }
        return $clauses;
    }
    
    public function insert($values = null)
    {
        return new Stato_Insert($this, $values);
    }
    
    public function select($columns = null, $whereClause = null)
    {
        return new Stato_Select($this->getClauseColumns($columns), $whereClause);
    }
    
    public function alias($as)
    {
        return new Stato_Alias($this, $as);
    }
}

class Stato_Alias extends Stato_TableClause
{
    public $table;
    public $columns;
    public $name;
    public $alias;
    
    public function __construct(Stato_TableClause $table, $alias)
    {
        $this->table = $table;
        $this->alias = $alias;
        $this->name = $alias;
        $this->columns = $table->columns;
    }
}

class Stato_Insert extends Stato_ClauseElement
{
    public $table;
    public $values;
    
    public function __construct(Stato_Table $table, $values = null)
    {
        $this->table = $table;
        $this->values = $values;
    }
    
    public function values($values)
    {
        $this->values = $values;
        return $this;
    }
}

class Stato_Select extends Stato_ClauseElement
{
    private $columns;
    
    private $froms;
    
    public function __construct(array $columns, $whereClause = null)
    {
        $this->froms = array();
        $this->columns = array();
        
        foreach ($columns as $c) {
            if ($c instanceof Stato_ClauseColumn)
                $this->columns[] = $c;
            else {
                $ref = new ReflectionObject($c);
                if ($ref->isSubclassOf(new ReflectionClass('Stato_TableClause')))
                    $this->columns = array_merge($this->columns, $c->getClauseColumns());
            }
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
}

class Stato_ClauseColumn extends Stato_ClauseElement
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
        return $this->compare(Stato_Operators::EQ, $other);
    }
    
    public function ne($other)
    {
        return $this->compare(Stato_Operators::NE, $other);
    }
    
    public function lt($other)
    {
        return $this->compare(Stato_Operators::LT, $other);
    }
    
    public function le($other)
    {
        return $this->compare(Stato_Operators::LE, $other);
    }
    
    public function gt($other)
    {
        return $this->compare(Stato_Operators::GT, $other);
    }
    
    public function ge($other)
    {
        return $this->compare(Stato_Operators::GE, $other);
    }
    
    public function like($other)
    {
        return $this->compare(Stato_Operators::LIKE, $other);
    }
    
    /**
     * Produces the clause LIKE '<$other>%'
     */
    public function startswith($other)
    {
        return $this->compare(Stato_Operators::LIKE, (string) $other.'%');
    }
    
    /**
     * Produces the clause LIKE '%<$other>'
     */
    public function endswith($other)
    {
        return $this->compare(Stato_Operators::LIKE, '%'.(string) $other);
    }
    
    /**
     * Produces the clause LIKE '%<$other>%'
     */
    public function contains($other)
    {
        return $this->compare(Stato_Operators::LIKE, '%'.(string) $other.'%');
    }
    
    protected function compare($op, $other)
    {
        if ($other === null)
            return new Stato_Expression($this, new Stato_Null, Stato_Operators::IS);
            
        $other = $this->checkLiteral($other);
        return new Stato_Expression($this, $other, $op);
    }
    
    protected function checkLiteral($other)
    {
        if (!$other instanceof Stato_ClauseColumn)
            return new Stato_BindParam(':'.$this->name, $other);
        return $other;
    }
}

class Stato_Expression extends Stato_ClauseElement
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
        $nop = Stato_Operators::negate($this->op);
        if (!$nop) return new Stato_UnaryExpression(new Stato_Grouping($this), Stato_Operators::NOT_);
        $this->op = $nop;
        return $this;
    }
}

class Stato_UnaryExpression extends Stato_ClauseElement
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

class Stato_ExpressionList extends Stato_ClauseElement
{
    public $expressions;
    public $operator;
    
    public function __construct(array $expressions, $operator = Stato_Operators::AND_)
    {
        $this->expressions = $expressions;
        $this->operator = $operator;
    }
}

class Stato_Grouping extends Stato_ClauseElement
{
    public $element;
    
    public function __construct($element)
    {
        $this->element = $element;
    }
}

class Stato_BindParam extends Stato_ClauseElement
{
    public $key;
    public $value;
    
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}

class Stato_Null extends Stato_ClauseElement
{
    
}