<?php



class BarPlugin extends Stato_Webflow_Plugin 
{
    public function postRouting()
    {
        $this->request->params['postRouting'] = 'Bar';
    }

    public function preDispatch()
    {
        if(is_array($this->request->params['preDispatch'])) {
            $params = $this->request->params['preDispatch'];
            $params[] = 'Bar';
            $this->request->params['preDispatch'] = $params;
        } else {
            $this->request->params['preDispatch'] = array('Bar');
        }

    }

}
