<?php

class RoutesTest extends PHPUnit_Framework_TestCase
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
        
        $this->assertEquals(array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $this->rec('posts/view/45'));
        $this->assertEquals(array('controller'=>'posts', 'action'=>'view'),
            $this->rec('posts/view'));
        $this->assertEquals(array('controller'=>'posts'),
            $this->rec('posts'));
        $this->assertEquals(array('controller'=>'users', 'action'=>'show'),
            $this->rec('users/show'));
            
        $this->assertEquals(array('posts/view/45', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'view', 'id'=>45)));
        $this->assertEquals(array('posts/list', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'list')));
        $this->assertEquals(array('posts', array()),
            $this->gen(array('controller'=>'posts')));
        $this->assertEquals(array('posts', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'index')));
        $this->assertEquals(array('users/show', array('group'=>'admin', 'pays'=>'france')),
            $this->gen(array('controller'=>'users', 'action'=>'show', 'group'=>'admin', 'pays'=>'france')));
    }
    
    function test_basic_rule_with_default_controller()
    {
        $map = new SRouteSet();
        $map->connect(':controller/:action/:id', array('controller'=>'blog'));
        $this->set_map($map);
        
        $this->assertEquals(array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $this->rec('posts/view/45'));
        $this->assertEquals(array('controller'=>'posts', 'action'=>'view'),
            $this->rec('posts/view'));
        $this->assertEquals(array('controller'=>'posts'),
            $this->rec('posts'));
        $this->assertEquals(array('controller'=>'blog'),
            $this->rec(''));
        $this->assertEquals(array('controller'=>'users', 'action'=>'show'),
            $this->rec('users/show'));
            
        $this->assertEquals(array('posts/view/45', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'view', 'id'=>45)));
        $this->assertEquals(array('posts/list', array()),
            $this->gen(array('controller'=>'posts', 'action'=>'list')));
        $this->assertEquals(array('users', array()),
            $this->gen(array('controller'=>'users')));
        $this->assertEquals(array('', array()),
            $this->gen(array('controller'=>'blog')));
        $this->assertEquals(array('blog/recent', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'recent')));
    }
    
    function test_empty_path()
    {
        $map = new SRouteSet();
        $map->connect('', array('controller'=>'blog', 'action'=>'recent'));
        $map->connect(':controller/:action/:id');
        $this->set_map($map);
        
        $this->assertEquals(array('controller'=>'blog', 'action'=>'recent'),
            $this->rec(''));
            
        $this->assertEquals(array('', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'recent')));
    }
    
    function test_simple_rewrites()
    {
        $map = new SRouteSet();
        $map->connect('test/:action', array('controller' => 'other'));
        $map->connect('posts/:category', array('controller'=>'blog', 'action'=>'posts', 'category'=>'all'));
        $map->connect(':controller/:action/:id');
        $this->set_map($map);
        
        $this->assertEquals(array('controller'=>'other', 'action'=>'list'),
            $this->rec('test/list'));
        $this->assertEquals(array('controller'=>'blog', 'action'=>'posts', 'category'=>'all'),
            $this->rec('posts'));
        $this->assertEquals(array('controller'=>'blog', 'action'=>'posts', 'category'=>'php'),
            $this->rec('posts/php'));
            
        $this->assertEquals(array('test/list', array()),
            $this->gen(array('controller'=>'other', 'action'=>'list')));
        $this->assertEquals(array('test', array()),
            $this->gen(array('controller'=>'other', 'action'=>'index')));
        $this->assertEquals(array('posts', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'posts')));
        $this->assertEquals(array('posts/php', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'posts', 'category'=>'php')));
    }
    
    function test_simple_resources_rewrites()
    {
        $map = new SRouteSet();
        $map->connect('users/:username', array('resource' => 'users'));
        $map->connect('posts/:category', array('resource' => 'posts', 'category'=>'all'));
        $map->connect('api/:resource/:id');
        $this->set_map($map);
        
        $this->assertEquals(array('resource' => 'users', 'username' => 'raphael'),
            $this->rec('users/raphael'));
        $this->assertEquals(array('resource' => 'posts', 'category' => 'all'),
            $this->rec('posts'));
        $this->assertEquals(array('resource' => 'posts', 'category'=>'php'),
            $this->rec('posts/php'));
        $this->assertEquals(array('resource' => 'comments', 'id'=>'1234'),
            $this->rec('api/comments/1234'));
    }
    
    function test_date_based()
    {
        $map = new SRouteSet();
        $map->connect('archives/:year/:month/:day', array('controller'=>'blog', 'action'=>'by_date',
            'month'=>null, 'day'=>null, 'requirements'=>array('year'=>'/\d{4}/', 'day'=>'/\d{1,2}/', 'month'=>'/\d{1,2}/')));
        $map->connect(':controller/:action/:id');
        $this->set_map($map);
        
        $this->assertEquals(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>02, 'day'=>14),
            $this->rec('archives/2006/02/14'));
        $this->assertEquals(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>02, 'day'=>null),
            $this->rec('archives/2006/02'));
        $this->assertEquals(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>null, 'day'=>null),
            $this->rec('archives/2006'));
            
        $this->assertEquals(array('archives/2006/02/14', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>'02', 'day'=>14)));
        $this->assertEquals(array('archives/2006/02', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>'02')));
        $this->assertEquals(array('archives/2006', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006)));
    }
    
    function test_modules()
    {
        $map = new SRouteSet();
        $map->connect('process/:controller/:action/:id', array('module'=>'processing', 'controller'=>'images', 'action'=>'list'));
        $map->connect('cms/:controller/:action/:id', array('module'=>'content', 'controller'=>'articles'));
        $map->connect('admin/users/:action/:id', array('module'=>'admin', 'controller' => 'users_roles'));
        $map->connect('admin/:controller/:action/:id', array('module'=>'admin'));
        $map->connect(':controller/:action/:id');
        $this->set_map($map);
        
        $this->assertEquals(array('module'=>'admin', 'controller'=>'users_roles', 'action'=>'edit', 'id'=>15),
            $this->rec('admin/users/edit/15'));
        $this->assertEquals(array('module'=>'admin', 'controller'=>'permissions', 'action'=>'edit', 'id'=>15),
            $this->rec('admin/permissions/edit/15'));
        $this->assertEquals(array('module'=>'content', 'controller'=>'photos', 'action'=>'edit', 'id'=>15),
            $this->rec('cms/photos/edit/15'));
        $this->assertEquals(array('module'=>'content', 'controller'=>'articles'),
            $this->rec('cms'));
        $this->assertEquals(array('module'=>'processing', 'controller'=>'images', 'action'=>'list'),
            $this->rec('process'));
        $this->assertEquals(array('module'=>'processing', 'controller'=>'images', 'action'=>'list'),
            $this->rec('process/images'));
        $this->assertEquals(array('module'=>'processing', 'controller'=>'images', 'action'=>'index'),
            $this->rec('process/images/index'));
        $this->assertEquals(array('module'=>'processing', 'controller'=>'pdf', 'action'=>'generate', 'id'=>15),
            $this->rec('process/pdf/generate/15'));
        $this->assertEquals(array('controller'=>'home', 'action'=>'about'),
            $this->rec('home/about'));
            
        $this->assertEquals(array('admin/users/edit/15', array()),
            $this->gen(array('module'=>'admin', 'controller'=>'users', 'action'=>'edit', 'id'=>15)));
        $this->assertEquals(array('cms/photos/edit/15', array()),
            $this->gen(array('module'=>'content', 'controller'=>'photos', 'action'=>'edit', 'id'=>15)));
        $this->assertEquals(array('cms/articles/edit/15', array()),
            $this->gen(array('module'=>'content', 'controller'=>'articles', 'action'=>'edit', 'id'=>15)));
        $this->assertEquals(array('cms', array()),
            $this->gen(array('module'=>'content', 'controller'=>'articles')));
        $this->assertEquals(array('cms', array()),
            $this->gen(array('module'=>'content', 'controller'=>'articles', 'action'=>'index')));
        $this->assertEquals(array('process', array()),
            $this->gen(array('module'=>'processing', 'controller'=>'images', 'action'=>'list')));
        $this->assertEquals(array('process/images/index', array()),
            $this->gen(array('module'=>'processing', 'controller'=>'images', 'action'=>'index')));
    }
    
    function test_path()
    {
        $map = new SRouteSet();
        $map->connect('articles/:action/:id', array('controller'=>'articles'));
        $map->connect('downloads/*filepath', array('controller'=>'downloads', 'action' => 'send_file'));
        $map->connect('*path', array('controller' => 'pages', 'action' => 'view'));
        $this->set_map($map);
        
        $this->assertEquals(array('controller'=>'articles', 'action'=>'edit', 'id'=>15),
            $this->rec('articles/edit/15'));
        $this->assertEquals(array('controller'=>'pages', 'action'=>'view', 'path'=>'products/web/cms/php'),
            $this->rec('products/web/cms/php'));
        $this->assertEquals(array('controller'=>'pages', 'action'=>'view', 'path'=>''),
            $this->rec(''));
        $this->assertEquals(array('controller'=>'downloads', 'action'=>'send_file', 'filepath'=>'pdf/my_book'),
            $this->rec('downloads/pdf/my_book'));
            
        $this->assertEquals(array('products/web/cms/php', array()),
            $this->gen(array('controller'=>'pages', 'action'=>'view', 'path'=>'products/web/cms/php')));
        $this->assertEquals(array('downloads/pdf/my_book', array()),
            $this->gen(array('controller'=>'downloads', 'action'=>'send_file', 'filepath'=>'pdf/my_book')));
    }
}

?>
