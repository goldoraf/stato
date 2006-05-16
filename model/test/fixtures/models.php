<?php

// For attribute access overloading test
class Bill extends SRecord
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
class Post extends SActiveRecord
{
    public $tableName = 'posts';
    public $recordTimestamps = True;
}

// For belongsTo tests
class Profile extends SActiveRecord
{
    public $tableName = 'profiles';
    public $relationships = array
    (
        'employe' => 'belongs_to'
    );
}

class Employe extends SActiveRecord
{
    public $tableName = 'employes';
}

// For hasMany tests
class Company extends SActiveRecord
{
    public $tableName = 'companies';
    public $relationships = array
    (
        'products' => 'has_many'
    );
}

class Product extends SActiveRecord
{
    public $tableName = 'products';
    public $attrRequired = array('name');
}

// For manyToMany tests
class Developer extends SActiveRecord
{
    public $tableName = 'developers';
    public $relationships = array
    (
        'projects' => 'many_to_many'
    );
}

class Project extends SActiveRecord
{
    public $tableName = 'projects';
    public $relationships = array
    (
        'developers' => 'many_to_many'
    );
}

// For hasOne tests
class Client extends SActiveRecord
{
    public $tableName = 'clients';
    public $relationships = array
    (
        'contract' => 'has_one'
    );
}

class Contract extends SActiveRecord
{
    public $tableName = 'contracts';
    public $attrRequired = array('code');
}

// For eager loading tests
class Article extends SActiveRecord
{
    public $tableName = 'articles';
    public $relationships = array
    (
        'comments' => 'has_many',
        'categories' => 'many_to_many'
    );
}

class Comment extends SActiveRecord
{
    public $tableName = 'comments';
    public $relationships = array
    (
        'article' => 'belongs_to'
    );
}

class Category extends SActiveRecord
{
    public $tableName = 'categories';
    public $relationships = array
    (
        'articles' => 'many_to_many'
    );
}

// For ListDecorator tests
class Forum extends SActiveRecord
{
    public $tableName = 'forums';
    public $relationships = array
    (
        'topics' => 'has_many'
    );
}

class Topic extends SActiveRecord
{
    public $tableName = 'topics';
}

// For validation tests
/*class User extends SActiveRecord
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
