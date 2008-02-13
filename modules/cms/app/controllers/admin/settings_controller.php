<?php

class SettingsController extends AdminBaseController
{
    public function index()
    {
        
    }
    
    public function update_settings()
    {
        foreach ($this->params['setting'] as $name => $value)
            Configuration::update_value($name, $value);
        
        $this->flash['notice'] = 'Paramètres enregistrés !';
        $this->redirect_to(array('action' => 'index'));
    }
}

?>
