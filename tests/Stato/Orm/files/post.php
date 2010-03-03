<?php

use Stato\Orm\ActiveRecord;

class Post extends ActiveRecord
{
    
}

class MyPost extends ActiveRecord
{
    protected static $tablename = 'posts';   
}