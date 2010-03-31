<?php

class Article
{
    public $id;
    public $title;
    public $body;
    public $published;
    public $created_on;
    public $price;
    
    public function __construct(array $values = array())
    {
        foreach ($values as $k => $v) $this->{$k} = $v;
    }
}

class ArticleWithPrivateProperties
{
    private $id;
    private $title;
    private $body;
    private $published;
    private $created_on;
    private $price;
    
    public function __construct(array $values = array())
    {
        foreach ($values as $k => $v) $this->{$k} = $v;
    }
}

class ArticleWithWeirdProperties
{
    public $i;
    public $t;
    
    public function __construct($title = null)
    {
        $this->t = $title;
    }
}