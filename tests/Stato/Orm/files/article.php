<?php

class Article
{
    public $id;
    public $title;
    public $body;
    public $published;
    public $created_on;
    public $price;
}

class ArticleWithPrivateProperties
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

class ArticleWithoutProperties
{
    
}

class ArticleWithWeirdProperties
{
    public $i;
    public $t;
}