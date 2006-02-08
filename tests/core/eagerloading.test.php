<?php

class EagerLoadingTest extends ActiveTestCase
{
    public $fixtures = array('articles', 'comments', 'categories', 'articles_categories');
    public $useInstantiatedFixtures = True;
                             
    function testLoadingWithOneAssociation()
    {
        $articles = SActiveStore::findAll('Article', Null, array('include' => array('comments')));
        $this->assertTrue($articles[0]->comments->isLoaded());
        $this->assertTrue($articles[1]->comments->isLoaded()); // the assoc must be flagged as loaded even if empty
        $this->assertEqual(2, $articles[0]->countComments());
        $this->assertEqual(0, $articles[1]->countComments());
        $this->assertTrue($articles[0]->comments->contains($this->comments['comment_1']));
        
        $article = SActiveStore::findFirst('Article', "articles.title='PHP6 overview'", array('include' => array('comments')));
        $this->assertTrue($article->comments->isLoaded());
        $this->assertEqual(2, $article->countComments());
        $this->assertTrue($article->comments->contains($this->comments['comment_1']));
    }
    
    function testLoadingWithMultipleAssociations()
    {
        $articles = SActiveStore::findAll('Article', Null, array('include' => array('comments', 'categories')));
        $this->assertTrue($articles[0]->comments->isLoaded());
        $this->assertEqual(2, $articles[0]->countComments());
        $this->assertTrue($articles[0]->categories->isLoaded());
        $this->assertEqual(3, $articles[0]->countCategories());
    }
}

?>
