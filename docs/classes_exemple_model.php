<?php

class Product extends Entity
{
    public $attributes = array
    (
        'name'  => 'string',
        'price' => 'float'
    );
    public $relationships = array
    (
        'group' => array
        (
            'type'     => 'to_one',
            'dest'     => 'ProductGroup',
            'required' => True,
        ),
        'details' => array
        (
            'type'       => 'to_many',
            'dest'       => 'ProductDetail',
            'max_occurs' => 3
        )
    );
}

class ProductGroup extends Entity
{
    public $attributes = array
    (
        'name'  => array
        (
            'type'        => 'string',
            'required'    => True,
            'unique'      => True,
            'validations' => array('isAlpha')
        )
    );
}

?>
