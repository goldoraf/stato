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

// For boolean and timestamps attributes tests
class Post extends SActiveRecord
{
    public static $objects;
    public $record_timestamps = True;
    public static $content_attributes_names = array('title');
}

class BooleanFalseByDefaultPost extends SActiveRecord
{
    public static $objects;
}

class DirtyPost extends SActiveRecord
{
    public static $objects;
    public static $table_name = 'posts';
}

class Employe extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('profiles' => 'has_many');
}

class Contract extends SActiveRecord
{
    public static $objects;
}

class Product extends SActiveRecord
{
    public static $objects;
}

class SuperProduct extends Product
{

}

class UserWithSerialization extends SActiveRecord
{
    public static $objects;
    public static $table_name = 'users';
    public $attr_serialized = array('preferences');
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
    public static $table_name = 'companies';
    public static $relationships = array
    (
        'products' => array('assoc_type' => 'has_many', 'dependent' => 'delete', 'foreign_key' => 'company_id')
    );
}
class DependentCompany2 extends SActiveRecord
{
    public static $objects;
    public static $table_name = 'companies';
    public static $relationships = array
    (
        'products' => array('assoc_type' => 'has_many', 'dependent' => 'delete_all', 'foreign_key' => 'company_id')
    );
}
class DependentCompany3 extends SActiveRecord
{
    public static $objects;
    public static $table_name = 'companies';
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
    
    public function __toString()
    {
        return $this->name;
    }
}

class Project extends SActiveRecord
{
    public static $objects;
    public static $relationships = array
    (
        'developers' => 'many_to_many',
        'client' => 'belongs_to'
    );
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
        'categories' => 'many_to_many',
        'author' => array('assoc_type' => 'belongs_to', 'class_name' => 'Developer', 'foreign_key' => 'author_id')
    );
}

class Comment extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('article' => 'belongs_to');
}

class Category extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('articles' => 'many_to_many');
    
    public function __toString()
    {
        return $this->name;
    }
}

// For ListDecorator tests
class Forum extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('topics' => 'has_many');
}

class Topic extends SActiveRecord
{
    public static $objects;
    public static $decorators = array('list' => array('scope' => 'forum'));
}

// For TreeDecorator tests
class Page extends SActiveRecord
{
    public static $objects;
    public static $decorators = array('tree' => array());
}

// For validation tests
class User extends SActiveRecord
{
    public static $objects;
    
    public function validate()
    {
        $this->validate_presence_of('username', 'password', 'mail');
        $this->validate_format_of('mail', array('pattern' => 'email'));
        $this->validate_format_of('password', array('pattern' => '/^[a-z0-9]{6,12}$/i', 'message' => 'Only alphanumerical characters plz !'));
        $this->validate_length_of('username', array('min_length' => 4, 'max_length' => 20, 'message' => '4 to 20 chars plz !'));
        $this->validate_inclusion_of('sex', array('choices' => array('M', 'F')));
    }
    
    public function validate_on_create()
    {
        $this->validate_confirmation_of('password', array('message' => 'Please confirm your password !'));
        $this->validate_acceptance_of('terms_of_services');
    }
}

// for inheritance tests
class Food extends SActiveRecord
{
    public static $objects;
    public static $relationships = array(
        'ingredients' => 'many_to_many',
        'chief' => 'belongs_to',
        'recipes' => 'has_many'
    );
}

class Pizza extends Food {}
class Burger extends Food {}
class MaxiBurger extends Burger {}

class Ingredient extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('foods' => 'many_to_many');
}

class Chief extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('foods' => 'has_many');
}

class Recipe extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('food' => 'belongs_to');
}

?>