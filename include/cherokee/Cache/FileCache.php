<?php
import('cherokee.Cache.Cache');

class FileCache extends Cache
{
    protected $cache_ext = '.cache';
    protected $lifetime = 0;
    
    public function setLifetime($time)
    {
        $this->lifetime = $time;
    }
    
    public function check()
    {
        return is_readable($this->cache_file)
               && filemtime($this->cache_file) >= filemtime($this->file) 
               && (!$this->lifetime || time() - filemtime($this->cache_file) <= $this->lifetime);
    }

    public function read()
    {
        $content = false;
        if ($this->check()) {
            $content = file_get_contents($this->cache_file);
        }
        
        return $content;
    }

    public function write($content)
    {
        file_put_contents($this->cache_file, $content);
    }
}
?>