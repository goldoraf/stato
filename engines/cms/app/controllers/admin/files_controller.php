<?php

class FilesController extends AdminBaseController
{
    private $root_path;
    
    public function initialize()
    {
        $this->root_path = STATO_APP_ROOT_PATH.'/public/documents';
    }
    
    public function index()
    {
        $this->root_dir = new RecursiveDirectoryIterator($this->root_path);
    }
    
    public function nodes()
    {
        if ($this->params['node'] == 'source') $path = '';
        else $path.= $this->params['node'];
            
        $this->render_json($this->nodes_for_js($path));
    }
    
    public function browse()
    {
        $this->layout = 'popup';
        $this->root_dir = new RecursiveDirectoryIterator($this->root_path);
    }
    
    public function create_dir()
    {
        $dir_name = SInflection::wikify($this->params['name']);
        SDir::mkdir($this->root_path.$this->params['parent'].'/'.$dir_name);
        $this->flash['notice'] = 'Dossier créé !';
        $this->redirect_to(array('action' => 'index'));
    }
    
    public function add_files()
    {
        foreach ($this->params['file'] as $file)
        {
            if (empty($file)) continue;
            
            $file_name = SInflection::sanitize_filename($file->name);
            $file->save($this->root_path.$this->params['parent'], $file_name);
        }
        $this->flash['notice'] = 'Fichier(s) transféré(s) !';
        $this->redirect_to(array('action' => 'index'));
    }
    
    public function delete()
    {
        // on résout le chemin canonique absolu
        $path = realpath(STATO_APP_ROOT_PATH.'/public/'.$this->params['file']);
        // on vérifie que le fichier existe est qu'il n'est pas en dehors du dossier d'upload
        if (!$path || preg_match('|^'.str_replace('\\', '/', realpath($this->root_path)).'|', str_replace('\\', '/', $path)) == 0)
        {
            $this->flash['notice'] = 'Impossible de supprimer le fichier !';
            $this->logger->info('Attention ! Tentative de suppression du fichier '.$path);
        }
        else
        {
            @unlink($path);
            $this->flash['notice'] = 'Fichier supprimé !';
        }
        $this->redirect_to(array('action' => 'index'));
    }
    
    private function nodes_for_js($path)
    {
        $nodes = array();
        $dir = new DirectoryIterator($this->root_path.'/'.$path);
        for ($dir->rewind(); $dir->valid(); $dir->next())
        {
            $file = $dir->getFilename();
            if ($dir->isDot() || $file == '.svn') continue;
            $nodes[] = array('id' => $file,
                             'path' => $path.'/'.$file,
                             'text' => $file,
                             'leaf' => !$dir->isDir() /*&& $dir->hasChildren()*/,
                             'cls' => ($dir->isFile()) ? file_css_class($file) : 'folder');
        }
        return $nodes;
    }
}

?>
