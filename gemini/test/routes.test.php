<?php

class RoutesTest extends UnitTestCase
{
    function set_map($map)
    {
        $map->draw();
        $this->map = $map;
    }
    
    function rec($url)
    {
        return $this->map->recognize_path($url);
    }
    
    function gen($options)
    {
        return $this->map->generate($options);
    }
    
    function test_basic_rule()
    {
        $map = new SRouteSet();
        $map->connect(':controller/:action/:id');
        $this->set_map($map);
        
        $this->assertEqual(array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $this->rec('posts/view/45'));
        $this->assertEqual(array('controller'=>'posts', 'action'=>'view'),
            $this->rec('posts/view'));
        $this->assertEqual(array('controller'=>'posts'),
            $this->rec('posts'));
        $this->assertEqual(array('controller'=>'users', 'action'=>'show'),
            $this->rec('users/show'));
            
        $this->assertEqual(array('posts/view/45', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'view', 'id'=>45)));
        $this->assertEqual(array('posts/list', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'list')));
        $this->assertEqual(array('posts', array()),
            $this->gen(array('controller'=>'posts')));
        $this->assertEqual(array('posts', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'index')));
        $this->assertEqual(array('users/show', array('group'=>'admin', 'pays'=>'france')),
            $this->gen(array('controller'=>'users', 'action'=>'show', 'group'=>'admin', 'pays'=>'france')));
    }
    
    function test_basic_rule_with_default_controller()
    {
        $map = new SRouteSet();
        $map->connect(':controller/:action/:id', array('controller'=>'blog'));
        $this->set_map($map);
        
        $this->assertEqual(array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $this->rec('posts/view/45'));
        $this->assertEqual(array('controller'=>'posts', 'action'=>'view'),
            $this->rec('posts/view'));
        $this->assertEqual(array('controller'=>'posts'),
            $this->rec('posts'));
        $this->assertEqual(array('controller'=>'blog'),
            $this->rec(''));
        $this->assertEqual(array('controller'=>'users', 'action'=>'show'),
            $this->rec('users/show'));
            
        $this->assertEqual(array('posts/view/45', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'view', 'id'=>45)));
        $this->assertEqual(array('posts/list', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'list')));
        $this->assertEqual(array('users', array()),
            $this->gen(array('controller'=>'users')));
        $this->assertEqual(array('', array()),
            $this->gen(array('controller'=>'blog')));
        $this->assertEqual(array('blog/recent', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'recent')));
    }
    
    function test_empty_path()
    {
        $map = new SRouteSet();
        $map->connect('', array('controller'=>'blog', 'action'=>'recent'));
        $map->connect(':controller/:action/:id');
        $this->set_map($map);
        
        $this->assertEqual(array('controller'=>'blog', 'action'=>'recent'),
            $this->rec(''));
            
        $this->assertEqual(array('', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'recent')));
    }
    
    function test_simple_rewrites()
    {
        $map = new SRouteSet();
        $map->connect('test/:action', array('controller' => 'other'));
        $map->connect('posts/:category', array('controller'=>'blog', 'action'=>'posts', 'category'=>'all'));
        $map->connect(':controller/:action/:id');
        $this->set_map($map);
        
        $this->assertEqual(array('controller'=>'other', 'action'=>'list'),
            $this->rec('test/list'));
        $this->assertEqual(array('controller'=>'blog', 'action'=>'posts', 'category'=>'all'),
            $this->rec('posts'));
        $this->assertEqual(array('controller'=>'blog', 'action'=>'posts', 'category'=>'php'),
            $this->rec('posts/php'));
            
        $this->assertEqual(array('test/list', array()),
            $this->gen(array('controller'=>'other', 'action'=>'list')));
        $this->assertEqual(array('test', array()),
            $this->gen(array('controller'=>'other', 'action'=>'index')));
        $this->assertEqual(array('posts', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'posts')));
        $this->assertEqual(array('posts/php', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'posts', 'category'=>'php')));
    }
    
    function test_simple_resources_rewrites()
    {
        $map = new SRouteSet();
        $map->connect('users/:username', array('resource' => 'users'));
        $map->connect('posts/:category', array('resource' => 'posts', 'category'=>'all'));
        $map->connect('api/:resource/:id');
        $this->set_map($map);
        
        $this->assertEqual(array('resource' => 'users', 'username' => 'raphael'),
            $this->rec('users/raphael'));
        $this->assertEqual(array('resource' => 'posts', 'category' => 'all'),
            $this->rec('posts'));
        $this->assertEqual(array('resource' => 'posts', 'category'=>'php'),
            $this->rec('posts/php'));
        $this->assertEqual(array('resource' => 'comments', 'id'=>'1234'),
            $this->rec('api/comments/1234'));
    }
    
    function test_date_based()
    {
        $map = new SRouteSet();
        $map->connect('archives/:year/:month/:day', array('controller'=>'blog', 'action'=>'by_date',
            'month'=>null, 'day'=>null, 'requirements'=>array('year'=>'/\d{4}/', 'day'=>'/\d{1,2}/', 'month'=>'/\d{1,2}/')));
        $map->connect(':controller/:action/:id');
        $this->set_map($map);
        
        $this->assertEqual(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>02, 'day'=>14),
            $this->rec('archives/2006/02/14'));
        $this->assertEqual(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>02, 'day'=>null),
            $this->rec('archives/2006/02'));
        $this->assertEqual(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>null, 'day'=>null),
            $this->rec('archives/2006'));
            
        $this->assertEqual(array('archives/2006/02/14', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>'02', 'day'=>14)));
        $this->assertEqual(array('archives/2006/02', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>'02')));
        $this->assertEqual(array('archives/2006', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006)));
    }
    
    function test_modules()
    {
        $map = new SRouteSet();
        $map->connect('process/:controller/:action/:id', array('module'=>'processing', 'controller'=>'images', 'action'=>'list'));
        $map->connect('cms/:controller/:action/:id', array('module'=>'content', 'controller'=>'articles'));
        $map->connect('admin/:controller/:action/:id', array('module'=>'admin'));
        $map->connect(':controller/:action/:id');
        $this->set_map($map);
        
        $this->assertEqual(array('module'=>'admin', 'controller'=>'users', 'action'=>'edit', 'id'=>15),
            $this->rec('admin/users/edit/15'));
        $this->assertEqual(array('module'=>'content', 'controller'=>'photos', 'action'=>'edit', 'id'=>15),
            $this->rec('cms/photos/edit/15'));
        $this->assertEqual(array('module'=>'content', 'controller'=>'articles'),
            $this->rec('cms'));
        $this->assertEqual(array('module'=>'processing', 'controller'=>'images', 'action'=>'list'),
            $this->rec('process'));
        $this->assertEqual(array('module'=>'processing', 'controller'=>'images', 'action'=>'list'),
            $this->rec('process/images'));
        $this->assertEqual(array('module'=>'processing', 'controller'=>'images', 'action'=>'index'),
            $this->rec('process/images/index'));
        $this->assertEqual(array('module'=>'processing', 'controller'=>'pdf', 'action'=>'generate', 'id'=>15),
            $this->rec('process/pdf/generate/15'));
            
        $this->assertEqual(array('admin/users/edit/15', array()),
            $this->gen(array('module'=>'admin', 'controller'=>'users', 'action'=>'edit', 'id'=>15)));
        $this->assertEqual(array('cms/photos/edit/15', array()),
            $this->gen(array('module'=>'content', 'controller'=>'photos', 'action'=>'edit', 'id'=>15)));
        $this->assertEqual(array('cms/articles/edit/15', array()),
            $this->gen(array('module'=>'content', 'controller'=>'articles', 'action'=>'edit', 'id'=>15)));
        $this->assertEqual(array('cms', array()),
            $this->gen(array('module'=>'content', 'controller'=>'articles')));
        $this->assertEqual(array('cms', array()),
            $this->gen(array('module'=>'content', 'controller'=>'articles', 'action'=>'index')));
        $this->assertEqual(array('process', array()),
            $this->gen(array('module'=>'processing', 'controller'=>'images', 'action'=>'list')));
        $this->assertEqual(array('process/images/index', array()),
            $this->gen(array('module'=>'processing', 'controller'=>'images', 'action'=>'index')));
    }
    
    function test_path()
    {
        $map = new SRouteSet();
        $map->connect('articles/:action/:id', array('controller'=>'articles'));
        $map->connect('downloads/*filepath', array('controller'=>'downloads', 'action' => 'send_file'));
        $map->connect('*path', array('controller' => 'pages', 'action' => 'view'));
        $this->set_map($map);
        
        $this->assertEqual(array('controller'=>'articles', 'action'=>'edit', 'id'=>15),
            $this->rec('articles/edit/15'));
        $this->assertEqual(array('controller'=>'pages', 'action'=>'view', 'path'=>'products/web/cms/php'),
            $this->rec('products/web/cms/php'));
        $this->assertEqual(array('controller'=>'pages', 'action'=>'view', 'path'=>''),
            $this->rec(''));
        $this->assertEqual(array('controller'=>'downloads', 'action'=>'send_file', 'filepath'=>'pdf/my_book'),
            $this->rec('downloads/pdf/my_book'));
            
        $this->assertEqual(array('products/web/cms/php', array()),
            $this->gen(array('controller'=>'pages', 'action'=>'view', 'path'=>'products/web/cms/php')));
        $this->assertEqual(array('downloads/pdf/my_book', array()),
            $this->gen(array('controller'=>'downloads', 'action'=>'send_file', 'filepath'=>'pdf/my_book')));
    }
}

?>
