<?php

use Stato\Orm\Entity;

class Post extends Entity
{
    
}

class MyPost extends Entity
{
    protected static $tablename = 'posts';   
}