<?php

namespace Stato\Orm;

use Post;

require_once __DIR__ . '/../TestsHelper.php';

require_once __DIR__ . '/files/post.php';

class ActiveRecordTest extends TestCase
{
    public function testPropertyAccess()
    {
        $post = new Post();
        $post->title = 'Test Driven Developement';
        $this->assertEquals('Test Driven Developement', $post->title);
    }
    
    protected function getDataSet()
    {
        $dataSet = new \PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        $dataSet->addTable('posts', __DIR__ . '/fixtures/posts.csv');
        return $dataSet;
    }
}