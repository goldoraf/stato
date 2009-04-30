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
    
    public function addColumn($tableName, Column $column);
    
    public function getColumnSpecification(Column $column);
    
    public function getDefaultValue(Column $column);
}