<?php

class PostsController extends AdminBaseController
{
    public function index()
    {
        $this->posts = Post::$objects->order_by('-created_on');
    }
    
    public function preview()
    {
        $this->layout = 'public';
        $this->post = Post::$objects->get($this->params['id']);
        $this->posts = Post::$objects->filter("published = '1'")
                                     ->limit($options['limit'])
                                     ->order_by('-created_on');
        $this->render_with_layout($this->template_path('pages', 'view_post'));
    }
    
    public function create()
    {
        if (!$this->request->is_post())
        {
            $this->post = new Post();
        }
        else
        {
            $this->post = new Post($this->params['post']);
            if ($this->post->save())
            {
                $this->sweep_cache();
                $this->flash['notice'] = 'Article créé !';
                $this->redirect_to(array('action' => 'index'));
                return;
            }
        }
    }
    
    public function update()
    {
        if (!$this->request->is_post())
        {
            $this->post = Post::$objects->get($this->params['id']);
        }
        else
        {
            $this->post = Post::$objects->get($this->params['post']['id']);
            if ($this->post->update_attributes($this->params['post']))
            {
                $this->sweep_cache();
                $this->flash['notice'] = 'Article édité !';
                $this->redirect_to(array('action' => 'index'));
                return;
            }
        }
    }
    
    public function delete()
    {
        Post::$objects->get($this->params['id'])->delete();
        $this->sweep_cache();
        $this->flash['notice'] = 'Article supprimé !';
        $this->redirect_to(array('action' => 'index'));
    }
    
    public function sweep_cache()
    {
        $this->expire_cache('/actualites');
        $this->expire_page(array('controller' => 'pages', 'action' => 'home'));
    }
}

?>
