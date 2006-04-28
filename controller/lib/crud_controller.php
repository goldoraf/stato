<?php

/**
 * Scaffolding controller class
 * 
 * This controller provide a series of actions for C.R.U.D (Create, Update, Delete)
 * operations on an Active Record class. Example :
 *<code>class WeblogController extends SCrudController
 *{
 *    public $scaffold = 'post';
 *}</code> 
 * The <var>$scaffold</var> is automatically converted to a class name, and used
 * for the instance variables names. The <var>renderScaffold()</var> method used by the controller
 * will first check to see if you've made your own template (like "weblog/index.php" 
 * for the index action) and if not, then render the generic template for that action.
 *  
 * @package Stato
 * @subpackage controller
 */
class SCrudController extends SActionController
{
    public $scaffold = null;
    
    private $scaffoldActions = array('index', 'view', 'add', 'create', 
                                     'edit', 'update', 'delete');
    
    public function __construct()
    {
        parent::__construct();
        if ($this->scaffold === null)
        {
            foreach ($this->scaffoldActions as $action)
            {
                $method = new ReflectionMethod(get_class($this), $action);
                if ($method->getDeclaringClass()->getName() == __CLASS__)
                    $this->hiddenActions[] = $action;
            }
        }
    }
    
    protected function initialize()
    {
        if ($this->scaffold !== null)
        {
            $this->models[] = $this->scaffold;
        
            if (strpos($this->scaffold, '/') !== false)
                list( , $this->scaffold) = explode('/', $this->scaffold);
            
            $this->class_name = ucfirst($this->scaffold);
            $this->singular_name = strtolower($this->scaffold);
            $this->plural_name = SInflection::pluralize($this->singular_name);
        }
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
            $this->redirectTo(array('action' => 'index'));
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
            $this->redirectTo(array('action' => 'index'));
        }
        else
        {
            $this->renderScaffold('edit');
        }
    }
    
    public function delete()
    {
        SActiveStore::findByPk($this->class_name, $this->params['id'])->delete();
        $this->redirectTo(array('action' => 'index'));
    }
    
    protected function instantiate($class, $values = Null)
    {
        return new $class($values);
    }
    
    protected function renderScaffold($action = Null)
    {
        if ($action == Null) $action = $this->actionName();
        $template = $this->templatePath($this->controllerName(), $action);
        if (file_exists($template)) $this->renderAction($action);
        else
        {
            $this->addVariablesToAssigns();
            $this->assigns['layout_content'] = $this->view->render($this->scaffoldPath($action), $this->assigns);
            if (!$this->layout)
                $this->renderFile($this->scaffoldPath('layout'));
            else
                $this->renderFile(APP_DIR.'/views/layouts/'.$this->layout.'.php');
        }
    }
    
    protected function scaffoldPath($templateName)
    {
        return ROOT_DIR."/core/controller/lib/templates/crud/{$templateName}.php";
    }
}

?>
