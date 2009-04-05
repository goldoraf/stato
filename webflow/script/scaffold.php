<?php

class ScaffoldCommand extends SCommand
{
    protected $allowed_params  = array('model_name' => true, 'controller_name' => false);
    protected $allowed_options = array('dry' => false);
    
    private $assigns  = array();
    private $scaffold = null;
    private $dry      = false;
    private $module   = '';
    
    public function execute()
    {
        $this->scaffold = $this->params['model_name'];
        
        if (isset($this->options['dry'])) $this->dry = true;
        
        if ($this->dry)
            $scaffold_templates_path = STATO_CORE_PATH.'/webflow/lib/templates/scaffolding_dry';
        else
            $scaffold_templates_path = STATO_CORE_PATH.'/webflow/lib/templates/scaffolding';
        
        if (strpos($this->scaffold, '/') !== false)
        {
            list($this->module, $this->scaffold) = explode('/', $this->scaffold);
            $this->test_module_existence($this->module);
            $base_path = "modules/{$this->module}";
        }
        else $base_path = 'app';
        
        if (!isset($this->params['controller_name']))
            $this->params['controller_name'] = SInflection::pluralize($this->scaffold);
        
        $this->assigns = array
        (
            'scaffold' => $this->scaffold,
            'controller_class_name' => SInflection::camelize($this->params['controller_name']).'Controller',
            'model_class_name' => SInflection::camelize($this->scaffold),
            'plural_us_name' => SInflection::pluralize($this->scaffold),
            'singular_us_name' => $this->scaffold,
            'singular_hm_name' => SInflection::humanize($this->scaffold),
            'plural_hm_name' => SInflection::humanize(SInflection::pluralize($this->scaffold))
        );
        
        $controller_path = "$base_path/controllers/".$this->params['controller_name'].'_controller.php';
        $this->create_file($controller_path, STATO_APP_ROOT_PATH,
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template("{$scaffold_templates_path}/controller.php", $this->assigns)
            )
        );
        
        $views_dir = "$base_path/views/".$this->params['controller_name'];
        $this->create_dir($views_dir, STATO_APP_ROOT_PATH);
        
        foreach (array('index', 'view', 'create', 'update') as $view_name)
        {
            $this->create_file("$views_dir/$view_name.php", STATO_APP_ROOT_PATH,
                SCodeGenerator::render_template("{$scaffold_templates_path}/views/{$view_name}.php", $this->assigns));
        }
        
        if (!$this->dry)
            $this->create_file("$views_dir/_form.php", STATO_APP_ROOT_PATH,
                SCodeGenerator::render_template("{$scaffold_templates_path}/views/_form.php", $this->assigns));
        
        $scaffold_layout_path = "views/layouts/scaffold.php";
        
        $this->create_file($scaffold_layout_path, STATO_APP_PATH,
            file_get_contents("{$scaffold_templates_path}/views/layout.php"));
    }
}

?>
