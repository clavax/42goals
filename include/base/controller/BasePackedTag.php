<?php
import('base.controller.BaseTag');
import('lib.json');

abstract class BasePackedTag extends BaseTag
{
    protected $url;
    protected $ext;
    
    protected $cache_dir;
    protected $template;
    
    public function getUrl($str)
    {
        $first = strtok($str, '.');
        $Conf = Framework::get('Conf');
        if (!$Conf->URL->has($first)) {
            $url = $Conf->URL->get($this->url) . $first;
        } else {
            $url = substr($Conf->URL->get($first), 0, -1);
        }
        
        while (($tok = strtok('.')) !== false) {
            $url .= '/' . $tok;
        }
        
        return $url . $this->ext;
    }
    
    public function handleFiles($name, array $files, $packed = 'no', $flush = false)
    {
        if (isset($packed) && $packed == 'yes') {
            $this->T->packed_name = $this->packFiles($name, $files, $flush);
        } else {
            foreach ($files as &$file) {
                $file = $this->getUrl($file);
            }
            $this->T->files = $files;
        }
        
        $this->T->packed = $packed;
        return $this->T->return($this->template);
    }    
    
    protected function packFiles($name, array $files, $flush = false)
    {
        $packed_path = $this->cache_dir . $name . '-' . $this->ENV->language . '-*' . $this->ext;
        $packed_files = glob($packed_path);
        if ($flush) {
            $to_delete = $packed_files;
        } else {
            if (arrays::nonempty($packed_files)) {
                return file::get_name(arrays::first($packed_files)); 
            }
        }
        $packed_path = $this->cache_dir . $name . '-' . $this->ENV->language . '-' . date('Y-m-d-H-i') . $this->ext;
        
        $packed_files = array();
        $content = '';
        foreach ($files as $file) {
            $content .= $this->pack($file, $packed_files);
        }
        file::put_contents($packed_path, $content);
        
        if ($flush) {
            foreach ($to_delete as $file) {
                file::delete($file);
            }
        }
        
        return file::get_name($packed_path);
    }
    
    abstract protected function pack($name, array &$files);
}
?>