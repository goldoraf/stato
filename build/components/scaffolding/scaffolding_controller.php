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
class ScaffoldingController extends SActionController
{
    public $scaffold = null;
    
    protected function initialize()
    {
        if ($this->params['scaffold'] !== null)
        {
            $this->scaffold = $this->params['scaffold'];
            SDependencies::requireDependencies('models', array($this->scaffold), get_class($this->parentController));
        
            if (strpos($this->scaffold, '/') !== false)
                list( , $this->scaffold) = explode('/', $this->scaffold);
            
            $this->class_name = SInflection::camelize($this->scaffold);
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
            $this->redirectTo(array('controller' => $this->parentController->controllerPath(),
                                    'action' => 'index'));
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
            $this->redirectTo(array('controller' => $this->parentController->controllerPath(),
                                    'action' => 'index'));
        }
        else
        {
            $this->renderScaffold('edit');
        }
    }
    
    public function delete()
    {
        SActiveStore::findByPk($this->class_name, $this->params['id'])->delete();
        $this->flash['notice'] = $this->class_name.' was successfully deleted !';
        $this->redirectTo(array('controller' => $this->parentController->controllerPath(),
                                'action' => 'index'));
    }
    
    protected function instantiate($class, $values = Null)
    {
        return new $class($values);
    }
    
    protected function renderScaffold($action = Null)
    {
        if ($action == Null) $action = $this->actionName();
        $this->addVariablesToAssigns();
        
        $template = $this->templatePath($this->parentController->controllerPath(), $action);
        if (file_exists($template)) 
            $this->assigns['layout_content'] = $this->view->render($template, $this->assigns);
        else
            $this->assigns['layout_content'] = $this->view->render($this->scaffoldPath($action), $this->assigns);
        
        if (!$this->parentController->layout)
            $this->renderFile($this->scaffoldPath('layout'));
        else
            $this->renderFile(APP_DIR.'/views/layouts/'.$this->parentController->layout.'.php');
    }
    
    protected function scaffoldPath($templateName)
    {
        return APP_DIR."/components/scaffolding/templates/{$templateName}.php";
    }
}

?>
