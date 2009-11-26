<?php

namespace Stato\Orm;

use Article;

require_once __DIR__ . '/../TestsHelper.php';

require_once __DIR__ . '/files/article.php';

class MapperTest extends TestCase
{
    protected $fixtures = array('articles');
    
    public function testBasicMapping()
    {
        $this->db->map('Article', 'articles');
        $article = $this->db->from('articles')->get(1);
        $this->assertEquals(1, $article->id);
        $this->assertEquals('Frameworks : A new hope...', $article->title);
        $this->assertEquals(new \DateTime('2008-12-01 20:30:00'), $article->created_on);
        $this->assertTrue($article->published);
        $this->assertEquals(2.99, $article->price);
    }
    
    public function testMappingWithPrivateProperties()
    {
        $this->db->map('ArticleWithPrivateProperties', 'articles');
        $article = $this->db->from('articles')->get(1);
        $this->assertEquals(array('id' => 1, 'title' => 'Frameworks : A new hope...', 'body' => 'bla bla bla',
            'published' => true, 'created_on' => new \DateTime('2008-12-01 20:30:00'), 'price' => 2.99), $article->toArray());
    }
}
