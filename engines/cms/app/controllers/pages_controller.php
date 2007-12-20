<?php

class PagesController extends ApplicationController
{
    protected $layout = 'public';
    protected $helpers = array('cms', 'rss');
    protected $components = array('mailer');
    protected $cached_pages = array('home', 'view', 'view_post');
    protected $cached_actions = array('rss');
    
    public function home()
    {
        $this->posts = Post::$objects->find_latest(Configuration::value('limit_post_home_page'));
    }
    
    public function view()
    {
        if (empty($this->params['path']))
            $this->content_page = Page::$objects->get_or_404("id = ?", array(Configuration::value('default_page')));
        else
            $this->content_page = Page::$objects->get_or_404("full_path = ?", array($this->params['path']));
    }
    
    public function view_post()
    {
        $this->post = Post::$objects->find_by_permalink(
            $this->params['year'], $this->params['month'], $this->params['day'], $this->params['permalink']
        );
        
        $this->posts = Post::$objects->find_latest(Configuration::value('limit_post_news_page'));
    }
    
    public function rss()
    {
        SActionController::$use_relative_urls = false;
        $this->posts = Post::$objects->find_latest(Configuration::value('limit_post_rss'));
        $this->render_xml();
    }
    
    public function contact()
    {
        if (!$this->request->is_post()) $this->message = new Message();
        else
        {
            $this->message = new Message($this->params['message']);
            if ($this->message->save())
            {
                $n = new Notifier();
                $n->deliver_request_for_contact($this->message);
                $this->render_action('contact_ok');
                return;
            }
        }
    }
}

?>
