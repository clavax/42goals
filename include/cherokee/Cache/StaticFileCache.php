<?php
import('cherokee.Cache.FileCache');
import('lib.file');

class StaticFileCache extends FileCache
{
    
    public function __construct($cache_dir, $file, $args = null)
    {
        $this->cache_dir = path($cache_dir, '/');
        if (is_array($args)) {
            ksort($args);
        }
        $this->args = $args;
        $this->cache_file = $this->prepare_cache_name($file, $this->args);
    }
    
    protected function prepare_cache_name($name, $args)
    {
        $args_hash = isset($args) ? '_' . str_replace('-', '_', crc32(serialize($args))) : '';
        $cache_file = preg_replace('/[^a-z_0-9]/i', '_', $name) . '_' . $args_hash;
        
        $cache_name = file::get_name($cache_file);
        $cache_ext  = file::get_ext($name);
        
        return $this->cache_dir . $cache_name . '.cache';
    }
}
?>