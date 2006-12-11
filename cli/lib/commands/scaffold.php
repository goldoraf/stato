<?php

class ScaffoldCommand extends SCommand
{
    protected $allowed_params  = array('model_name' => true, 'controller_name' => false);
    protected $allowed_options = array('ajax' => false);
    
    private $assigns  = array();
    private $scaffold = null;
    private $ajax     = false;
    private $sub_dir  = '';
    
    public function execute()
    {
        $this->scaffold = $this->params['model_name'];
        
        if (isset($this->options['ajax'])) $this->ajax = true;
        
        if ($this->ajax)
            $scaffold_templates_path = STATO_CORE_PATH.'/cli/lib/templates/scaffolding_ajax';
        else
            $scaffold_templates_path = STATO_CORE_PATH.'/cli/lib/templates/scaffolding';
        
        if (strpos($this->scaffold, '/') !== false)
        {
            list($this->sub_dir, $this->scaffold) = explode('/', $this->scaffold);
            $this->test_module_existence($sub_dir);
            $this->sub_dir.= '/';
        }
        
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
        
        $controller_path = STATO_APP_PATH."/controllers/{$this->sub_dir}".$this->params['controller_name'].'_controller.php';
        $this->test_file_existence($controller_path);
        file_put_contents($controller_path,
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template("{$scaffold_templates_path}/controller.php", $this->assigns)
            )
        );
        
        $views_dir = STATO_APP_PATH."/views/{$this->sub_dir}".$this->params['controller_name'];
        $this->test_folder_existence($views_dir);
        SDir::mkdir($views_dir);
        
        foreach (array('index', 'view', 'create', 'update') as $view_name)
        {
            file_put_contents("$views_dir/$view_name.php",
                SCodeGenerator::render_template("{$scaffold_templates_path}/views/{$view_name}.php", $this->assigns));
        }
        
        if ($this->ajax)
        {
            file_put_contents("$views_dir/_objects_list.php",
                SCodeGenerator::render_template("{$scaffold_templates_path}/views/_objects_list.php", $this->assigns));
        }
        
        if ($this->ajax)
            $scaffold_layout_path = STATO_APP_PATH."/views/layouts/scaffold_ajax.php";
        else
            $scaffold_layout_path = STATO_APP_PATH."/views/layouts/scaffold.php";
        
        if (!file_exists($scaffold_layout_path))
            @copy("{$scaffold_templates_path}/views/layout.php", $scaffold_layout_path);
            
        if ($this->ajax)
        {
            $scaffold_helper_path = STATO_APP_PATH."/helpers/scaffold_ajax_helper.php";
            if (!file_exists($scaffold_helper_path))
                @copy("{$scaffold_templates_path}/helper.php", $scaffold_helper_path);
        }
    }
}

?>
