<?php
abstract class Cache extends Object
{
    protected $cache_dir;  // directory where cached file is stored
    protected $cache_file; // cached file
    protected $cache_ext;  // cache file extension
    protected $file;       // file path that is needed to be cached
    protected $args;       // file arguments

    public function __construct($cache_dir, $file, $args = null)
    {
        $this->cache_dir = path($cache_dir, '/');
        $this->file = path($file);
        if (is_array($args)) {
            ksort($args);
        }
        $this->args = $args;
        $this->cache_file = $this->prepare_cache_name($this->file, $this->args);
    }

    protected function prepare_cache_name($name, $args)
    {
        $file_path = realpath($name);
        $file_name = basename($file_path);
        $dir_name  = dirname($file_path);

        $path_hash = str_replace('-', '_', crc32($dir_name));
        $args_hash = isset($args) ? '_' . str_replace('-', '_', crc32(serialize($args))) : '';
        
        $cache_name = preg_replace('/[^a-z_0-9]/i', '_', $file_name) . '_' . $path_hash . $args_hash;
        
        return $this->cache_dir . $cache_name . $this->cache_ext;
    }
    
    public function get_name()
    {
        return preg_replace('/^' . preg_quote($this->cache_dir, '/') . '/', '', $this->cache_file, 1);
    }

    abstract public function read();
    
    abstract public function write($content);
    
    abstract public function check();
    
    public function __get($name)
    {
        $value = null;
        
        switch ($name) {
        case 'name':
            $value = $this->get_name();
            break;
            
        case 'content':
            $value = $this->read();
            break;
            
        case 'status':
            $value = $this->check();
            break;
            
        default:
            $value = parent::__get($name);
        }
        
        return $value;
    }
    
    public function __set($name, $value)
    {
        switch ($name) {
        case 'content':
            $this->write($value);
            break;
            
        default:
        }
    }
}
?>