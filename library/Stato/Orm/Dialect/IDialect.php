<?php

namespace Stato\Orm\Dialect;

use Stato\Orm\Table;
use Stato\Orm\Column;

interface IDialect
{
    public function getDsn(array $params);
    
    public function getDriverOptions();
    
    public function getTableNames(\PDO $connection);
    
    public function reflectTable(\PDO $connection, $tableName);
    
    public function createTable(Table $table);
    
    public function dropTable($tableName);
    
    public function createDatabase($dbName);
    
    public function dropDatabase($dbName);
    
    public function addColumn($tableName, Column $column);
    
    public function getColumnSpecification(Column $column);
    
    public function getDefaultValue(Column $column);
}

interface IType
{
    public function getBindProcessor();
    
    public function getResultProcessor();
}

class GenericType implements IType
{
    public function getBindProcessor()
    {
        return function($value) { return $value; };
    }
    
    public function getResultProcessor()
    {
        return function($value) { return $value; };
    }
}

class Integer implements IType
{
    public function getBindProcessor()
    {
        return function($value) { return $value; };
    }
    
    public function getResultProcessor()
    {
        return function($value) { return (int) $value; };
    }
}

class Float implements IType
{
    public function getBindProcessor()
    {
        return function($value) { return $value; };
    }
    
    public function getResultProcessor()
    {
        return function($value) { return (float) $value; };
    }
}

class DateTime implements IType
{
    public function getBindProcessor()
    {
        return function($value) { return $value->format('Y-m-d H:i:s'); };
    }
    
    public function getResultProcessor()
    {
        return function($value) { return new \DateTime($value); };
    }
}