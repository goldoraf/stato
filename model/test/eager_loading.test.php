<?php

class EagerLoadingTest extends ActiveTestCase
{
    public $fixtures = array('articles', 'comments', 'categories', 'articles_categories');
                             
    public function test_loading_with_one_association()
    {
        $articles = Article::$objects->includes('comments')->dump();
        $this->assertTrue($articles[0]->comments->is_loaded());
        $this->assertTrue($articles[1]->comments->is_loaded()); // the assoc must be flagged as loaded even if empty
        $this->assertEqual(2, $articles[0]->comments->count());
        $this->assertEqual(0, $articles[1]->comments->count());
        
        $i = 0;
        foreach ($articles[0]->comments->all() as $c) { $i++; }
        $this->assertEqual(2, $i);
        
        $comments = $articles[0]->comments->dump();
        $this->assertEqual('xxx@yyy.com', array_shift($comments)->author);
        
        $article = Article::$objects->includes('comments')->get("articles.title='PHP6 overview'");
        $this->assertTrue($article->comments->is_loaded());
        $this->assertEqual(2, $article->comments->count());
        $comments = $article->comments->dump();
        $this->assertEqual('xxx@yyy.com', array_shift($comments)->author);
    }
    
    public function test_loading_with_multiple_associations()
    {
        $articles = Article::$objects->includes('comments', 'categories')->dump();
        $this->assertTrue($articles[0]->comments->is_loaded());
        $this->assertEqual(2, $articles[0]->comments->count());
        $this->assertTrue($articles[0]->categories->is_loaded());
        $this->assertEqual(3, $articles[0]->categories->count());
    }
}

?>
