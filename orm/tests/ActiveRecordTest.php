<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'connection.php';
require_once 'databases/mysql.php';
require_once 'expression.php';
require_once 'schema.php';
require_once 'compiler.php';
require_once 'helpers.php';
require_once 'mapper.php';
require_once 'query.php';
require_once 'active_record.php';

require_once dirname(__FILE__) . '/files/post.php';

class Stato_ActiveRecordTest extends Stato_DatabaseTestCase
{
    public function testPropertyAccess()
    {
        $post = new Post();
        $post->title = 'Test Driven Developement';
        $this->assertEquals('Test Driven Developement', $post->title);
    }
    
    protected function getDataSet()
    {
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        $dataSet->addTable('posts', dirname(__FILE__) . '/fixtures/posts.csv');
        return $dataSet;
    }
}