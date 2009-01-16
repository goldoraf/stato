<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'view.php';

class Stato_ViewTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->view = new Stato_View();
        $this->view->addPath(dirname(__FILE__).'/files/views');
    }
    
    public function testRenderFile()
    {
        $this->assertEquals('hello world', $this->view->render(dirname(__FILE__).'/files/views/foo.php'));
    }
        
    public function testRenderFileWithAssigns()
    {
        $this->view->assign(array('username' => 'raphael'));
        $this->assertEquals('hello raphael', $this->view->render(dirname(__FILE__).'/files/views/bar.php'));
    }
    
    public function testRenderFileWithLayout()
    {
        $this->assertEquals(
            '<html><body>hello world</body></html>', 
            $this->view->render(dirname(__FILE__).'/files/views/foo.php', array('layout' => 'main'))
        );
    }
        
    public function testRenderFileWithAssignsAndLayout()
    {
        $this->view->assign(array('username' => 'raphael'));
        $this->assertEquals(
            '<html><body>hello raphael</body></html>', 
            $this->view->render(dirname(__FILE__).'/files/views/bar.php', array('layout' => 'main'))
        );
    }
    
    public function testRenderMissingFile()
    {    
        $this->setExpectedException('Stato_MissingTemplate');
        $this->view->render(dirname(__FILE__).'/files/views/dummy.php');
    }
    
    public function testRenderTemplate()
    {
        $this->assertEquals('hello world', $this->view->render('foo'));
    }
    
    public function testRenderTemplateWithLayout()
    {
        $this->assertEquals(
            '<html><body>hello world</body></html>', 
            $this->view->render('foo', array('layout' => 'main'))
        );
    }
    
    public function testRenderMissingTemplate()
    {    
        $this->setExpectedException('Stato_MissingTemplate');
        $this->view->render('dummy');
    }
    
    public function testRenderTemplateCollection()
    {
        $this->assertEquals(
            '<li>foo 1</li><li>bar 2</li><li>baz 3</li>',
            $this->view->render('_item', array('collection' => array('foo', 'bar', 'baz')))
        );
    }
    
    public function testRenderTemplateCollectionWithSpacer()
    {
        $this->assertEquals(
            '<li>foo 1</li><br /><li>bar 2</li><br /><li>baz 3</li>',
            $this->view->render('_item', array('collection' => array('foo', 'bar', 'baz'), 'spacer' => '<br />'))
        );
    }
    
    public function testRenderTemplateCollectionWithSpacerTemplate()
    {
        $this->assertEquals(
            '<li>foo 1</li><br /><li>bar 2</li><br /><li>baz 3</li>',
            $this->view->render('_item', array('collection' => array('foo', 'bar', 'baz'), 'spacer_template' => '_spacer'))
        );
    }
}