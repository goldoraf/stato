<?php

require_once(CORE_DIR.'/common/common.php');

class SRoutesTest extends UnitTestCase
{
    function setMap($map)
    {
        $map->draw();
        $this->map = $map;
    }
    
    function rec($url)
    {
        return $this->map->recognizePath($url);
    }
    
    function gen($options)
    {
        return $this->map->generate($options);
    }
    
    function testBasicRule()
    {
        $map = new SRouteSet();
        $map->connect(':controller/:action/:id');
        $this->setMap($map);
        
        $this->assertEqual(array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $this->rec('posts/view/45'));
        $this->assertEqual(array('controller'=>'posts', 'action'=>'view'),
            $this->rec('posts/view'));
        $this->assertEqual(array('controller'=>'posts'),
            $this->rec('posts'));
        $this->assertEqual(array('controller'=>'users', 'action'=>'show', 'group'=>'admin', 'pays'=>'france'),
            $this->rec('users/show?group=admin&pays=france'));
            
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
    
    function testBasicRuleWithDefaultController()
    {
        $map = new SRouteSet();
        $map->connect(':controller/:action/:id', array('controller'=>'blog'));
        $this->setMap($map);
        
        $this->assertEqual(array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $this->rec('posts/view/45'));
        $this->assertEqual(array('controller'=>'posts', 'action'=>'view'),
            $this->rec('posts/view'));
        $this->assertEqual(array('controller'=>'posts'),
            $this->rec('posts'));
        $this->assertEqual(array('controller'=>'blog'),
            $this->rec(''));
        $this->assertEqual(array('controller'=>'users', 'action'=>'show', 'group'=>'admin', 'pays'=>'france'),
            $this->rec('users/show?group=admin&pays=france'));
            
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
    
    function testEmptyPath()
    {
        $map = new SRouteSet();
        $map->connect('', array('controller'=>'blog', 'action'=>'recent'));
        $map->connect(':controller/:action/:id');
        $this->setMap($map);
        
        $this->assertEqual(array('controller'=>'blog', 'action'=>'recent'),
            $this->rec(''));
            
        $this->assertEqual(array('', array()),
            $this->gen(array('controller'=>'blog', 'action'=>'recent')));
    }
    
    function testSimpleRewrites()
    {
        $map = new SRouteSet();
        $map->connect('test/:action', array('controller' => 'other'));
        $map->connect('posts/:category', array('controller'=>'blog', 'action'=>'posts', 'category'=>'all'));
        $map->connect(':controller/:action/:id');
        $this->setMap($map);
        
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
    
    function testDateBased()
    {
        $map = new SRouteSet();
        $map->connect('archives/:year/:month/:day', array('controller'=>'blog', 'action'=>'by_date',
            'month'=>null, 'day'=>null, 'requirements'=>array('year'=>'/\d{4}/', 'day'=>'/\d{1,2}/', 'month'=>'/\d{1,2}/')));
        $map->connect(':controller/:action/:id');
        $this->setMap($map);
        
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
    
    function testControllerInSubdir()
    {
        $map = new SRouteSet();
        $map->connect('process/:controller/:action/:id', array('subdirectory'=>'processing', 'controller'=>'images', 'action'=>'list'));
        $map->connect('cms/:controller/:action/:id', array('subdirectory'=>'content', 'controller'=>'articles'));
        $map->connect('admin/:controller/:action/:id', array('subdirectory'=>'admin'));
        $map->connect(':controller/:action/:id');
        $this->setMap($map);
        
        $this->assertEqual(array('controller'=>'admin/users', 'action'=>'edit', 'id'=>15),
            $this->rec('admin/users/edit/15'));
        $this->assertEqual(array('controller'=>'content/photos', 'action'=>'edit', 'id'=>15),
            $this->rec('cms/photos/edit/15'));
        $this->assertEqual(array('controller'=>'content/articles'),
            $this->rec('cms'));
        $this->assertEqual(array('controller'=>'processing/images', 'action'=>'list'),
            $this->rec('process'));
        $this->assertEqual(array('controller'=>'processing/images', 'action'=>'list'),
            $this->rec('process/images'));
        $this->assertEqual(array('controller'=>'processing/images', 'action'=>'index'),
            $this->rec('process/images/index'));
        $this->assertEqual(array('controller'=>'processing/pdf', 'action'=>'generate', 'id'=>15),
            $this->rec('process/pdf/generate/15'));
            
        $this->assertEqual(array('admin/users/edit/15', array()),
            $this->gen(array('controller'=>'admin/users', 'action'=>'edit', 'id'=>15)));
        $this->assertEqual(array('cms/photos/edit/15', array()),
            $this->gen(array('controller'=>'content/photos', 'action'=>'edit', 'id'=>15)));
        $this->assertEqual(array('cms/articles/edit/15', array()),
            $this->gen(array('controller'=>'content/articles', 'action'=>'edit', 'id'=>15)));
        $this->assertEqual(array('cms', array()),
            $this->gen(array('controller'=>'content/articles')));
        $this->assertEqual(array('cms', array()),
            $this->gen(array('controller'=>'content/articles', 'action'=>'index')));
        $this->assertEqual(array('process', array()),
            $this->gen(array('controller'=>'processing/images', 'action'=>'list')));
        $this->assertEqual(array('process/images/index', array()),
            $this->gen(array('controller'=>'processing/images', 'action'=>'index')));
    }
}

?>
