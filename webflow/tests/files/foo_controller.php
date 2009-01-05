<?php

class FooController extends Stato_Controller
{
    protected $layout = 'main';
    
    public function bar()
    {
        $this->response->setBody('hello world');
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
        $this->render(self::FILE, dirname(__FILE__).'/views/foo.php');
    }
    
    public function renderFileWithAssigns()
    {
        $this->username = 'raphael';
        $this->render(self::FILE, dirname(__FILE__).'/views/bar.php');
    }
    
    public function renderMissingFile()
    {
        $this->render(self::FILE, dirname(__FILE__).'/views/dummy.php');
    }
    
    public function renderSimpleTemplate()
    {
        $this->render(self::TEMPLATE, 'foo/bar');
    }
    
    public function renderMissingTemplate()
    {
        $this->render(self::TEMPLATE, 'foo/dummy');
    }
}
