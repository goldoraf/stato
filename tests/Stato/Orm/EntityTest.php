<?php

namespace Stato\Orm;

use Post, MyPost;

require_once __DIR__ . '/../TestsHelper.php';

require_once __DIR__ . '/files/post.php';

class EntityTest extends TestCase
{
    protected $fixtures = array('posts');
    
    public function testPropertyAccess()
    {
        $post = new Post();
        $post->title = 'Test Driven Developement';
        $this->assertEquals('Test Driven Developement', $post->title);
    }
    
    public function testInstanciation()
    {
        $post = new Post(array('title' => 'Test Driven Developement'));
        $this->assertEquals('Test Driven Developement', $post->title);
    }
    
    public function testGetTableName()
    {
        $this->assertEquals('posts', Post::getTablename());
        $this->assertEquals('posts', MyPost::getTablename());
    }
    
    public function testGet()
    {
        $post = Post::get(1);
        $this->assertEquals('Frameworks : A new hope...', $post->title);
    }
    
    public function testSaveNewObject()
    {
        $post = new Post(array('title' => 'Test Driven Developement'));
        $post->text = 'blablabla';
        $this->assertTrue($post->isNew());
        $post->save();
        $this->assertEquals(3, $post->id);
        $this->assertFalse($post->isNew());
        $reloaded_post = Post::get(3);
        $this->assertEquals($post->title, $reloaded_post->title);
    }
    
    public function testUpdateObject()
    {
        $post = Post::get(1);
        $post->title = 'A new framework...';
        $post->save();
        $reloaded_post = Post::get(1);
        $this->assertEquals('A new framework...', $reloaded_post->title);
    }
    
    public function testDeleteObject()
    {
        $post = Post::get(1);
        $post->delete();
        $this->setExpectedException('\Stato\Orm\RecordNotFound');
        $post = Post::get(1);
    }
    
    public function testFilterBy()
    {
        $posts = Post::filterBy(array('author' => 'John Doe'))->toArray();
        $this->assertEquals('Frameworks : A new hope...', $posts[0]->title);
        $this->assertEquals('PHP6 and namespaces', $posts[1]->title);
    }
    
    public function testFilterWithClosure()
    {
        $posts = Post::filter(function($p) { return $p->author->like('%Doe'); })->toArray();
        $this->assertEquals('Frameworks : A new hope...', $posts[0]->title);
        $this->assertEquals('PHP6 and namespaces', $posts[1]->title);
    }
}