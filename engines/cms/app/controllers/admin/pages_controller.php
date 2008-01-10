<?php

class PagesController extends AdminBaseController
{   
    public function index()
    {
        
    }
    
    public function test_extjs2()
    {
        $this->layout = 'test_extjs2';
    }
    
    public function details()
    {
        $this->page = Page::$objects->get($this->params['id']);
        $this->render_json(array('success' => true, 'data' => $this->page->to_array()));
    }
    
    public function info()
    {
        $this->page = Page::$objects->get($this->params['id']);
        $this->render_partial('info');
    }
    
    public function nodes()
    {
        if ($this->params['node'] != 'source')
            $pages = Page::$objects->get($this->params['node'])->children->all();
        else
            $pages = Page::$objects->roots();
        
        $this->render_json($this->pages_for_js($pages));
    }
    
    public function sort_tree()
    {
        $page = Page::$objects->get($this->params['id']);
        if ($page->parent_id != $this->params['new_parent_id'])
        {
            $page->remove_from_list(); // and so we update siblings positions
            if ($this->params['new_parent_id'] == 'source')
                $page->parent_id = null;
            else
                $page->parent_id = $this->params['new_parent_id'];
        }
        $page->add_to_list_bottom();
        $page->insert_at($this->params['index'] + 1);
        
        $this->expire_cache();
        $this->render_nothing();
    }
    
    public function as_list()
    {
        $this->roots = Page::$objects->roots();
    }
    
    public function browse()
    {
        $this->layout = 'popup';
        $this->roots = Page::$objects->roots();
        $this->root_dir = new RecursiveDirectoryIterator(STATO_APP_ROOT_PATH.'/public/documents');
    }
    
    public function preview()
    {
        $this->layout = 'public';
        $this->content_page = Page::$objects->get($this->params['id']);
        $this->render_with_layout($this->template_path('pages', 'view'));
    }
    
    public function create()
    {
        if (!$this->request->is_post()) $this->page = new Page();
        else
        {
            $this->page = Page::$objects->create($this->params['page']);
            if ($this->page->save())
            {
                $this->expire_cache();
                $this->flash['notice'] = 'Page créée !';
                $this->redirect_to(array('action' => 'index'));
                return;
            }
        }
    }
    
    public function update()
    {
        if (!$this->request->is_post())
        {
            $this->page = Page::$objects->get($this->params['id']);
        }
        else
        {
            $this->page = Page::$objects->get($this->params['page']['id']);
            if ($this->page->update_attributes($this->params['page']))
            {
                $this->expire_cache();
                $this->flash['notice'] = 'Page mise à jour !';
                $this->redirect_to(array('action' => 'index'));
                return;
            }
        }
    }
    
    public function up()
    {
        Page::$objects->get($this->params['id'])->move_higher();
        $this->redirect_to(array('action' => 'index'));
    }
    
    public function delete()
    {
        Page::$objects->get($this->params['id'])->delete();
        $this->expire_cache();
        $this->flash['notice'] = 'Page supprimée !';
        $this->redirect_to(array('action' => 'index'));
    }
    
    private function pages_for_js($qs)
    {
        $pages = array();
        foreach ($qs as $p)
            $pages[] = array('id' => $p->id,
                             'path' => $p->full_path,
                             'cls' => ($p->published) ? 'pub-page' : 'unpub-page',
                             'text' => $p->title,
                             'leaf' => ($p->children->count() == 0));
        return $pages;
    }
}

?>
