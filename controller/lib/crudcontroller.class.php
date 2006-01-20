<?php

class CRUDController extends ActionController
{
    public $model = Null;
    
    public function __construct()
    {
        if ($this->model == Null)
        {
            $this->model = str_replace('controller', '', strtolower(get_class($this)));
        }
        $model = $this->model;
        $this->useModels[] = $model;
        
        parent::__construct();
        
        $this->response['entity_class'] = $this->model;
    }
    
    public function index()
    {
        $this->response['entities'] = ActiveStore::findAll($this->model);
        $this->renderScaffold();
    }
    
    public function view()
    {
        $this->response['entity'] = ActiveStore::findByPk($this->model, $this->params['id']);
        $this->renderScaffold();
    }
    
    public function add()
    {
        $class = $this->model;
        
        if ($this->request->isPost())
        {
            $entity = new $class($this->params[$this->model]);
            if ($entity->save()) $this->redirect('index');
        }
        else
        {
            $entity = new $class();
        }
        $this->response[$this->model] = $entity;
        $this->renderScaffold();
    }
    
    public function edit()
    {
        $this->response[$this->model] = ActiveStore::findByPk($this->model, $this->params['id']);
        $this->renderScaffold();
    }
    
    public function update()
    {
        $params = $this->params[$this->model];
        $entity = ActiveStore::findByPk($this->model, $this->params[$this->model]['id']);
        $entity->populate($params);
        $entity->save();
        $this->redirect('index');
    }
    
    public function delete()
    {
        $entity = ActiveStore::findByPk($this->model, $this->params['id']);
        $entity->delete();
        $this->redirect('index');
    }
    
    public function renderScaffold()
    {
        $action = $this->request->action;
        $path = $this->getDefaultTemplatePath();
        if (!file_exists($path)) $path = ROOT_DIR."/core/view/templates/crud/{$action}.php";
        $this->renderFile($path);
    }
}

?>
