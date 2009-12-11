<?php

use Stato\Orm\Entity;

class Article extends Entity
{
    public $id;
    public $title;
    public $body;
    public $published;
    public $created_on;
    public $price;
}

class ArticleWithPrivateProperties extends Entity
{
    private $id;
    private $title;
    private $body;
    private $published;
    private $created_on;
    private $price;
    
    public function toArray()
    {
        return array('id' => $this->id, 'title' => $this->title, 'body' => $this->body,
            'published' => $this->published, 'created_on' => $this->created_on, 'price' => $this->price);
    }
}

class ArticleWithoutProperties extends Entity
{
    
}

class ArticleWithWeirdProperties extends Entity
{
    public $i;
    public $t;
}