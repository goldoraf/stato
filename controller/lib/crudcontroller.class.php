<?php

class SCrudController extends SActionController
{
    public $model = Null;
    
    public function __construct()
    {
        if ($this->model == Null)
            $this->model = SInflection::singularize(str_replace('controller', '', strtolower(get_class($this))));
        
        $this->useModels[] = $this->model;
        
        parent::__construct();
        
        $this->class_name = ucfirst($this->model);
        $this->singular_name = strtolower($this->model);
        $this->plural_name = SInflection::pluralize($this->singular_name);
    }
    
    public function index()
    {
        $this->{$this->plural_name} = SActiveStore::findAll($this->class_name);
        $this->renderScaffold();
    }
    
    public function view()
    {
        $this->{$this->singular_name} = SActiveStore::findByPk($this->class_name, $this->params['id']);
        $this->renderScaffold();
    }
    
    public function add()
    {
        $this->{$this->singular_name} = $this->instantiate($this->class_name);
        $this->renderScaffold();
    }
    
    public function create()
    {
        $this->{$this->singular_name} = $this->instantiate($this->class_name, $this->params[$this->singular_name]);
        if ($this->{$this->singular_name}->save())
        {
            $this->flash['notice'] = $this->class_name.' was successfully created !';
            $this->redirect('index');
        }
        else
        {
            $this->renderScaffold('add');
        }
    }
    
    public function edit()
    {
        $this->{$this->singular_name} = SActiveStore::findByPk($this->class_name, $this->params['id']);
        $this->renderScaffold();
    }
    
    public function update()
    {
        $this->{$this->singular_name} = SActiveStore::findByPk($this->class_name, $this->params[$this->singular_name]['id']);
        if ($this->{$this->singular_name}->updateAttributes($this->params[$this->singular_name]))
        {
            $this->flash['notice'] = $this->class_name.' was successfully updated !';
            $this->redirect('index');
        }
        else
        {
            $this->renderScaffold('edit');
        }
    }
    
    public function delete()
    {
        SActiveStore::findByPk($this->class_name, $this->params['id'])->delete();
        $this->redirect('index');
    }
    
    protected function instantiate($class, $values = Null)
    {
        return new $class($values);
    }
    
    protected function renderScaffold($action = Null)
    {
        if (!$this->flash->isEmpty()) $this->response['flash'] = $this->flash->dump();
        $this->flash->discard();
        
        if ($action == Null) $action = $this->request->action;
        $path = $this->defaultTemplatePath();
        if (!file_exists($path)) $path = ROOT_DIR."/core/view/templates/crud/{$action}.php";
        
        foreach($this->useHelpers as $helper) $this->requireHelper($helper);
        
        $renderer = new SRenderer($path, $this->response->values);
        
        if (!$this->layout)
            $layout = ROOT_DIR."/core/view/templates/crud/layout.php";
        else
            $layout = APP_DIR.'/layouts/'.$this->layout.'.php';
            
        if (!file_exists($layout)) throw new SException('Layout not found');
        $this->response['layout_content'] = $renderer->render();
        $renderer = new SRenderer($layout, $this->response->values);
        
        $this->renderText($renderer->render());
    }
}

?>
