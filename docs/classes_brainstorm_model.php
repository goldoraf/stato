<?php

class Post extends Entity
{
    public $relationships = array
    (
        'author' => array('type' => 'to_one', 'dest' => 'Author');
    );
}

class Author extends Entity { }

class Product extends Entity
{
    public $attributes = array
    (
        'name'  => 'string',
        'stock' => 'int',
        'price' => 'float' // on utilise ici la notation simplifiée
    );
    public $relationships = array
    (
        'group' => array('type' => 'to_one', 'dest' => 'ProductGroup');
    );
}

return array
(
    'Product' => array
    (
        'class' => 'product_desc',
        'attributes' => array
        (
            'stock' => array('name' => 'stock', 'node' => 'attribute'),
            'price' => 'vat_free_price'
        )
    )
);

class Invoice extends Entity
{
    public $relationships = array
    (
        'shippingAdress' => array('type' => 'to_one', 'dest' => 'ShippingAdress'),
        'items' => array('type' => 'to_many', 'dest' => 'Item')
    );
}

class Item extends Entity
{
    public $primaryKey = 'sku';
    public $attributes = array
    (
        'name'        => 'string',
        'quantity'    => 'int',
        'price'       => 'float', // à améliorer
        'description' => 'string'
    );
}

class ShippingAdress extends Entity
{
    public $attributes = array
    (
        'name'   => 'string',
        'street' => 'string',
        'city'   => 'string',
        'state'  => array('type' => 'string', 'pattern' => '[A-Z]{2}'),
        'zip'    => array('type' => 'string', 'pattern' => '[0-9]{5}(-[0-9]{4})?')
    );
}

?>
