<?php
import('cherokee.Cache.FileCache');

class DynamicFileCache extends FileCache
{
    const ENDL = "\n\r";
    
    protected $condition;
    
    public function __construct($cache_dir, $file, $args = null, $condition = null)
    {
        parent::__construct($cache_dir, $file, $args);
        
        $this->prepare_condition($condition);
    }
    
    protected function prepare_condition($condition)
    {
        $this->condition = $condition;
    }
    
    public function check()
    {
        if (!parent::check()) {
            return false;
        }
        
        if ($f = fopen($this->cache_file, 'r')) {
            $header = trim(fgets($f));
            $check = unserialize($header);
            if ($check != $this->condition) {
                return false;
            }
            fclose($f);
        }
        
        return true;
    }
    
    public function read()
    {
        if (!parent::check()) {
            return false;
        }
        
        $content = false;
        if ($f = fopen($this->cache_file, 'r')) {
            $header = trim(fgets($f));
            $check = unserialize($header);
            if ($check != $this->condition) {
                return false;
            }

            $content = '';
            while (!feof($f)) {
                $content .= fgets($f);
            }
            fclose($f);
        }
        return $content;
    }
    
    public function write($content)
    {
        file_put_contents($this->cache_file, serialize($this->condition) . self::ENDL . $content);
    }
}
?>