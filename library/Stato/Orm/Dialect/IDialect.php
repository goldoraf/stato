<?php

namespace Stato\Orm\Dialect;

use Stato\Orm\Table;
use Stato\Orm\Column;
use Stato\Orm\Connection;

interface IDialect
{
    public function getDsn(array $params);
    
    public function getDriverOptions();
    
    public function getTableNames(Connection $connection);
    
    public function reflectTable(Connection $connection, $tableName);
    
    public function createTable(Table $table);
    
    public function truncateTable($tableName);
    
    public function dropTable($tableName);
    
    public function createDatabase($dbName);
    
    public function dropDatabase($dbName);
    
    public function addColumn($tableName, Column $column);
    
    public function getColumnSpecification(Column $column);
    
    public function getDefaultValue(Column $column);
}
