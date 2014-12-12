<?php
import('base.controller.BasePage');

class DownloadPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        
        $this->addRule(
            array(
                'file' => '(\d+): id'
            )
        );
    }
    
    public function handleFile()
    {
        import('lib.file');
        
        $TasksFiles = new PrimaryTable('tasks_files', 'id');
        $file = $TasksFiles->select(array('name', 'tmp_name'), SQL::quote('id = ?', $this->ENV->id), null, 1);
        if (!$file) {
            return 'ID does not exist';
        }
        
        $path = $this->PTH->tmp . 'upload/' . $file['tmp_name'];
        if (!file_exists($path)) {
            return 'File does not exist ' . $path;
        }
        
        file::download($path, $file['name']);
        return true;
    }
}
?>