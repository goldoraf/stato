<?php

ini_set("include_path", "d:/xampp/stato_core");

require('./mercury/lib/column.php');
require('./mercury/lib/adapters/abstract.php');
require('./mercury/lib/adapters/mysql.php');

class Table
{
    private $name;
    private $alias;
    private $adapter;
    private $columns;
    private $fks;
    
    public function __construct($name)
    {
        $this->name = $name;
        $this->alias = null;
        $this->adapter = new SMySqlAdapter(array(
            'host'    => 'localhost',
            'user'    => 'stato',
            'dbname'  => 'sqldsl_tests'
        ));
        $this->columns = $this->adapter->columns($name);
        $this->fks = array();
    }
    
    public function __get($column_name)
    {
        return new ColumnDecorator($this->columns[$column_name], $this->name_or_alias());
    }
    
    public function add_fk($foreign_table, $column_name)
    {
        $this->fks[$foreign_table] = $column_name;
    }
    
    public function get_fk_on($table_name)
    {
        return $this->fks[$table_name];
    }
    
    public function name()
    {
        return $this->name;
    }
    
    public function alias($name = null)
    {
        $this->alias = $name;
        return $this;
    }
    
    public function reference()
    {
        $ref = $this->name;
        if ($this->alias !== null) $ref.= ' AS '.$this->alias;
        return $ref;
    }
    
    private function name_or_alias()
    {
        return ($this->alias !== null) ? $this->alias : $this->name;
    }
}

class ColumnDecorator
{
    private $column;
    private $table_ref;
    
    public function __construct($column, $table_ref)
    {
        $this->column = $column;
        $this->table_ref = $table_ref;
    }
    
    public function __toString()
    {
        return $this->table_ref.'.'.$this->column->name;
    }
    
    public function __is_identical($value)
    {
        return $this->get_condition('=', $value);
    }
    
    public function __is_smaller_or_equal($value)
    {
        return $this->get_condition('<=', $value);
    }
    
    private function get_condition($operator, $value)
    {
        return new Condition(implode(' ', array($this->__toString(), $operator, '"'.$value.'"')));
    }
}

class Condition
{
    private $sql;
    
    public function __construct($sql)
    {
        $this->sql = $sql;
    }
    
    public function __bw_or($condition)
    {
        $this->sql.= ' OR '.$condition->__toString();
        return $this;
    }
    
    public function __toString()
    {
        return $this->sql;
    }
}

class Select
{
    private $table;
    private $condition;
    private $joins = array();
    
    public function __construct($table)
    {
        $this->table = $table;
    }
    
    public function join($table)
    {
        $fk = $table->get_fk_on($this->table->name());
        $this->joins[] = 'LEFT OUTER JOIN '.$table->reference().' ON '.($this->table->id === $table->$fk);
        return $this;
    }
    
    public function where($condition)
    {
        $this->condition = $condition;
        return $this;
    }
    
    public function __toString()
    {
        $sql = 'SELECT * FROM '.$this->table->reference();
        if (!empty($this->joins))
            $sql.= ' '.implode(' ', $this->joins);
        $sql.= ' WHERE '.$this->condition->__toString();
        return $sql;
    }
}

function select($table)
{
    return new Select($table);
}

/*
 CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `fullname` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 CREATE TABLE `adresses` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  INDEX (user_id),
  FOREIGN KEY (user_id) REFERENCES users (id),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
*/
$users    = new Table('users');
$adresses = new Table('adresses');
$adresses->add_fk('users', 'user_id');

echo select($users)->where($users->name === 'Raph')."\n";
echo select($users)->where($users->id <= 5)."\n";
echo select($users)->where(($users->name === 'Raph') | ($users->id <= 5))."\n";

$u = $users->alias('u');

echo select($u)->where($u->name === 'Raph')."\n";

echo select($u)->join($adresses)->where($u->name === 'Raph')."\n";

?>