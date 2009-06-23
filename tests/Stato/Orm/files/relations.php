<?php

use Stato\Orm\Entity;
use Stato\Orm\Column;
use Stato\Orm\Relation;

class Company extends Entity
{
    
}

class Product extends Entity
{
    protected static $relations = array(
        'companies' => 'Company'
    );
}