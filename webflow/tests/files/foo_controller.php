<?php

class FooController extends Stato_Controller
{
    protected $layout = 'main';
    
    public function bar()
    {
        $this->response->setBody('hello world');
    }
    
    public function bat()
    {
        
    }
    
    public function baz()
    {
        $this->render();
    }
    
    public function renderSimpleText()
    {
        $this->render(self::TEXT, 'hello world');
    }
    
    public function renderTextWithStatus()
    {
        $this->render(self::TEXT, 'hello world', array('status' => 500));
    }
    
    public function renderTextWithLayout()
    {
        $this->render(self::TEXT, 'hello world', array('layout' => true));
    }
    
    public function renderSimpleFile()
    {
        $this->render(self::TEMPLATE, dirname(__FILE__).'/views/foo.php');
    }
    
    public function renderFileWithAssigns()
    {
        $this->username = 'raphael';
        $this->render(self::TEMPLATE, dirname(__FILE__).'/views/bar.php');
    }
    
    public function renderMissingFile()
    {
        $this->render(self::TEMPLATE, dirname(__FILE__).'/views/dummy.php');
    }
    
    public function renderSimpleTemplate()
    {
        $this->render(self::TEMPLATE, 'foo/bar');
    }
    
    public function renderMissingTemplate()
    {
        $this->render(self::TEMPLATE, 'foo/dummy');
    }
    
    public function renderSpecificAction()
    {
        $this->render(self::ACTION, 'baz');
    }
}
