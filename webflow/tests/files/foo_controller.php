<?php

class FooController extends Stato_Controller
{
    public function index()
    {
        return 'hello world';
    }
    
    public function foo()
    {
        return $this->render();
    }
    
    public function bar()
    {
        $this->setLayout('main');
        return $this->render();
    }
    
    public function baz()
    {
        return $this->render('foo', array('status' => 204));
    }
    
    public function respondText()
    {
        $this->respond('hello world');
    }
    
    public function respondTextWithStatus()
    {
        $this->respond('hello world', 500);
    }
    
    public function simpleRedirect()
    {
        $this->redirect('/posts/1234');
    }
    
    public function redirectPermanently()
    {
        $this->redirect('/posts/1234', true);
    }
    
    public function renderSpecificFile()
    {
        return $this->render(array('template' => dirname(__FILE__).'/views/foo.php'));
    }
    
    public function renderSpecificFileWithAssigns()
    {
        $this->username = 'raphael';
        return $this->render(array('template' => dirname(__FILE__).'/views/bar.php'));
    }
    
    public function renderSpecificFileWithLayout()
    {
        return $this->render(array('template' => dirname(__FILE__).'/views/foo.php', 'layout' => 'main'));
    }
    
    public function renderSpecificFileWithAssignsAndLayout()
    {
        $this->username = 'raphael';
        return $this->render(array('template' => dirname(__FILE__).'/views/bar.php', 'layout' => 'main'));
    }
    
    public function renderMissingFile()
    {
        return $this->render(array('template' => dirname(__FILE__).'/views/dummy.php'));
    }
    
    public function renderSpecificTemplate()
    {
        return $this->render('foo/bar');
    }
    
    public function renderSpecificTemplateWithLayout()
    {
        return $this->render('foo/bar', array('layout' => 'main'));
    }
    
    public function renderMissingTemplate()
    {
        return $this->render('foo/dummy');
    }
    
    public function renderAction()
    {
        return $this->render('baz');
    }
    
    public function renderWithoutArguments()
    {
        return $this->render();
    }
    
    public function partialTemplateCollection()
    {
        return $this->partial('_item', array('collection' => array('foo', 'bar', 'baz')));
    }
    
    public function partialTemplateCollectionWithSpacer()
    {
        return $this->partial('_item', array('collection' => array('foo', 'bar', 'baz'), 'spacer' => '<br />'));
    }
    
    public function partialTemplateCollectionWithSpacerTemplate()
    {
        return $this->partial('_item', array('collection' => array('foo', 'bar', 'baz'), 'spacer_template' => '_spacer'));
    }
}
