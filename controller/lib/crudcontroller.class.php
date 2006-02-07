<?php

class CRUDController extends ActionController
{
    public $model = Null;
    
    public function __construct()
    {
        if ($this->model == Null)
            $this->model = str_replace('controller', '', strtolower(get_class($this)));
        
        $this->useModels[] = $this->model;
        
        parent::__construct();
        
        $this->class_name = $this->model;
        $this->singular_name = strtolower($this->model);
        $this->plural_name = Inflection::pluralize($this->singular_name);
    }
    
    public function index()
    {
        $this->entities = ActiveStore::findAll($this->class_name);
    }
    
    public function view()
    {
        $this->entity = ActiveStore::findByPk($this->class_name, $this->params['id']);
    }
    
    public function create()
    {
        $class = $this->class_name;
        
        if ($this->request->isPost())
        {
            $entity = new $class($this->params[$this->class_name]);
            if ($entity->save())
            {
                $this->flash['notice'] = $this->singular_name.' was successfully created !';
                $this->redirect('index');
            }
        }
        else
        {
            $entity = new $class();
        }
        $this->response[$this->class_name] = $entity;
    }
    
    public function update()
    {
        $class = $this->class_name;
        
        if ($this->request->isPost())
        {
            $entity = ActiveStore::findByPk($this->class_name, $this->params[$this->class_name]['id']);
            if ($entity->updateAttributes($this->params[$this->class_name]))
            {
                $this->flash['notice'] = $this->singular_name.' was successfully updated !';
                $this->redirect('index');
            }
        }
        else
        {
            $entity = ActiveStore::findByPk($this->class_name, $this->params['id']);
        }
        $this->response[$this->class_name] = $entity;
    }
    
    public function delete()
    {
        $entity = ActiveStore::findByPk($this->class_name, $this->params['id']);
        $entity->delete();
        $this->redirect('index');
    }
    
    public function render()
    {
        if (!$this->flash->isEmpty()) $this->response['flash'] = $this->flash->dump();
        $this->flash->discard();
        
        $action = $this->request->action;
        $path = $this->defaultTemplatePath();
        if (!file_exists($path)) $path = ROOT_DIR."/core/view/templates/crud/{$action}.php";
        
        foreach($this->useHelpers as $helper) $this->requireHelper($helper);
        
        $renderer = new Renderer($path, $this->response->values);
        
        if (!$this->layout)
            $layout = ROOT_DIR."/core/view/templates/crud/layout.php";
        else
            $layout = APP_DIR.'/layouts/'.$this->layout.'.php';
            
        if (!file_exists($layout)) throw new Exception('Layout not found');
        $this->response['layout_content'] = $renderer->render();
        $renderer = new Renderer($layout, $this->response->values);
        
        $this->renderText($renderer->render());
    }
}

?>
