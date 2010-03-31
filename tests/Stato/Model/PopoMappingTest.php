<?php

namespace Stato\Model;

require_once __DIR__ . '/TestsHelper.php';

require_once __DIR__ . '/models/Article.php';

use Article, ArticleWithPrivateProperties, ArticleWithWeirdProperties;

class PopoMappingTest extends TestCase
{
    public function setup()
    {
        parent::setup();
        $this->repository = Repository::get('default');
    }
    
    public function testSimpleMappingWithReflection()
    {
        $this->repository->map('Article', 'articles');
        $article = new Article(array('title' => 'Frameworks : A new hope...', 'body' => 'bla bla bla',
            'published' => true, 'created_on' => new \DateTime('2008-12-01 20:30:00'), 'price' => 2.99));
        $this->repository->create($article);
        $articleReloaded = $this->repository->from('articles')->get(1);
        $this->assertEquals($article, $articleReloaded);
    }
    
    public function testMappingPrivateProperties()
    {
        $this->repository->map('ArticleWithPrivateProperties', 'articles');
        $article = new ArticleWithPrivateProperties(array('title' => 'Frameworks : A new hope...', 'body' => 'bla bla bla',
            'published' => true, 'created_on' => new \DateTime('2008-12-01 20:30:00'), 'price' => 2.99));
        $this->repository->create($article);
        $articleReloaded = $this->repository->from('articles')->get(1);
        $this->assertEquals($article, $articleReloaded);
    }
    
    public function testClassicMapping()
    {
        $this->repository->map('ArticleWithWeirdProperties', 'articles', array(
            'properties' => array(
                'i' => new Property('i', Metaclass::SERIAL, array('column' => 'id')),
                't' => new Property('t', Metaclass::STRING, array('column' => 'title'))
            )
        ));
        $article = new ArticleWithWeirdProperties('Frameworks : A new hope...');
        $this->repository->create($article);
        $articleReloaded = $this->repository->from('articles')->get(1);
        $this->assertEquals($article, $articleReloaded);
    }
    
    public function testIncludePropertiesOption()
    {
        $this->repository->map('Article', 'articles', array('include_properties' => array('id', 'title')));
        $article = new Article(array('title' => 'Frameworks : A new hope...', 'body' => 'bla bla bla',
            'published' => true, 'created_on' => new \DateTime('2008-12-01 20:30:00'), 'price' => 2.99));
        $this->repository->create($article);
        $articleReloaded = $this->repository->from('articles')->get(1);
        $this->assertEquals(new Article(array('id' => 1, 'title' => 'Frameworks : A new hope...')), $articleReloaded);
    }
    
    public function testExcludePropertiesOption()
    {
        $this->repository->map('Article', 'articles', array('exclude_properties' => array('body', 'created_on', 'price')));
        $article = new Article(array('title' => 'Frameworks : A new hope...', 'body' => 'bla bla bla',
            'published' => true, 'created_on' => new \DateTime('2008-12-01 20:30:00'), 'price' => 2.99));
        $this->repository->create($article);
        $articleReloaded = $this->repository->from('articles')->get(1);
        $this->assertEquals(new Article(array('id' => 1, 'title' => 'Frameworks : A new hope...', 'published' => true)), $articleReloaded);
    }
}