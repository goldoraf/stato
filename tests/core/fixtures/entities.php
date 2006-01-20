<?php

// For attribute access overloading test
class Bill extends Entity
{
    public $attributes = array
    (
        'product'     => 'string',
        'price'       => 'float',
        'total'       => 'float'
    );
    public static $tax = '0.2';
    
    public function readTotal()
    {
        return $this->values['price'] + $this->values['price'] * self::$tax;
    }
}

// For boolean et timestamps attributes tests
class Post extends ActiveEntity
{
    public $tableName = 'posts';
    public $recordTimestamps = True;
}

// For belongsTo tests
class Profile extends ActiveEntity
{
    public $tableName = 'profiles';
    public $relationships = array
    (
        'employe' => array('type' => 'to_one', 'dest' => 'Employe')
    );
}

class Employe extends ActiveEntity
{
    public $tableName = 'employes';
}

// For hasMany tests
class Company extends ActiveEntity
{
    public $tableName = 'companies';
    public $relationships = array
    (
        'products' => array('type' => 'to_many', 'dest' => 'Product')
    );
}

class Product extends ActiveEntity
{
    public $tableName = 'products';
    public $attrRequired = array('name');
}

// For manyToMany tests
class Developer extends ActiveEntity
{
    public $tableName = 'developers';
    public $relationships = array
    (
        'projects' => array('type' => 'to_many', 'dest' => 'Project', 'inverse' => True)
    );
}

class Project extends ActiveEntity
{
    public $tableName = 'projects';
    public $relationships = array
    (
        'developers' => array('type' => 'to_many', 'dest' => 'Developer', 'inverse' => True)
    );
}

// For oneToOne tests
class Client extends ActiveEntity
{
    public $tableName = 'clients';
    public $relationships = array
    (
        'contract' => array('type' => 'to_one', 'dest' => 'Contract', 'inverse' => True)
    );
}

class Contract extends ActiveEntity
{
    public $tableName = 'contracts';
    public $attrRequired = array('code');
    public $relationships = array
    (
        'client' => array('type' => 'to_one', 'dest' => 'Client', 'inverse' => True)
    );
}

// For eager loading tests
class Article extends ActiveEntity
{
    public $tableName = 'articles';
    public $relationships = array
    (
        'comments' => array('type' => 'to_many', 'dest' => 'Comment'),
        'categories' => array('type' => 'to_many', 'dest' => 'Category', 'inverse' => True)
    );
}

class Comment extends ActiveEntity
{
    public $tableName = 'comments';
    public $relationships = array
    (
        'article' => array('type' => 'to_one', 'dest' => 'Article')
    );
}

class Category extends ActiveEntity
{
    public $tableName = 'categories';
    public $relationships = array
    (
        'articles' => array('type' => 'to_many', 'dest' => 'Article', 'inverse' => True)
    );
}

// For ActAsList tests
class Forum extends ActiveEntity
{
    public $tableName = 'forums';
    public $relationships = array
    (
        'topics' => array('type' => 'to_many', 'dest' => 'Topic')
    );
}

class Topic extends ActiveEntity
{
    public $tableName = 'topics';
    public $actAs = array('List' => array('scope' => 'forum'));
}

// For validation tests
/*class User extends ActiveEntity
{
    public $tableName = 'users';
    
    public $attributes = array
    (
        'id'         => 'integer',
        'nick_name'  => 'string',
        'password'   => 'string',
        'sex'        => 'string',
        'email'      => 'string'
    );
    
    public $validations = array
    (
        'nick_name' => array
        (
            'format' => array('pattern' => 'alphanum'),
            'length' => array('max_length' => 30)
        ),
        'password' => array
        (
            'format'       => array('pattern' => 'alphanum', 'message' => 'Only alphanumerical characters plz !'),
            'length'       => array('min_length' => 6, 'max_length' => 15, 'wrong_size' => '6 to 15 chars plz !'),
            'confirmation' => array('on' => 'create', 'message' => 'Please confirm your password !')
        ),
        'sex' => array
        (
            'inclusion' => array('choices' => array('M', 'F'))
        )
    );
}*/

?>
