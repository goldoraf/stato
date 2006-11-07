<?php

// For attribute access overloading test
class Bill extends SActiveRecord
{
    public static $tax = '0.2';
    
    public static function objects()
    {
        return new SManager('Bill');
    }
    
    public function read_total()
    {
        return $this->values['price'] + $this->values['price'] * self::$tax;
    }
    
    public function write_total($value)
    {
        $this->values['price'] = $value / (1 + self::$tax); 
    }
}

// For boolean et timestamps attributes tests
class Post extends SActiveRecord
{
    public static $objects;
    public $recordTimestamps = True;
}

class Employe extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('profiles' => 'has_many');
}

class Contract extends SActiveRecord
{
    public static $objects;
    public $attrRequired = array('code');
}

class Product extends SActiveRecord
{
    public static $objects;
    public $attrRequired = array('name');
}

class SuperProduct extends Product
{

}

// For belongsTo tests
class Profile extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('employe' => 'belongs_to');
}

// For SManager, hasMany and hasManyThrough tests
class Company extends SActiveRecord
{
    public static $objects;
    public static $relationships = array
    (
        'products' => 'has_many',
        'employes' => 'has_many',
        'profiles' => array('assoc_type' => 'has_many', 'through' => 'employes')
    );
}
class DependentCompany1 extends SActiveRecord
{
    public static $objects;
    public static $tableName = 'companies';
    public static $relationships = array
    (
        'products' => array('assoc_type' => 'has_many', 'dependent' => 'delete', 'foreign_key' => 'company_id')
    );
}
class DependentCompany2 extends SActiveRecord
{
    public static $objects;
    public static $tableName = 'companies';
    public static $relationships = array
    (
        'products' => array('assoc_type' => 'has_many', 'dependent' => 'delete_all', 'foreign_key' => 'company_id')
    );
}
class DependentCompany3 extends SActiveRecord
{
    public static $objects;
    public static $tableName = 'companies';
    public static $relationships = array
    (
        'products' => array('assoc_type' => 'has_many', 'dependent' => 'nullify', 'foreign_key' => 'company_id')
    );
}

// For manyToMany tests
class Developer extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('projects' => 'many_to_many');
}

class Project extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('developers' => 'many_to_many');
}

// For hasOne tests
class Client extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('contract' => 'has_one');
}

// For eager loading tests
class Article extends SActiveRecord
{
    public static $objects;
    public static $relationships = array
    (
        'comments' => 'has_many',
        'categories' => 'many_to_many'
    );
}

class Comment extends SActiveRecord
{
    public static $objects;
    public static $relationships = array
    (
        'article' => 'belongs_to'
    );
}

class Category extends SActiveRecord
{
    public static $objects;
    public static $relationships = array
    (
        'articles' => 'many_to_many'
    );
}

// For ListDecorator tests
/*class Forum extends SActiveRecord
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
}*/

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
