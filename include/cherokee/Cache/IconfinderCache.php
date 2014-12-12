<?php
import('cherokee.Cache.FileCache');

class IconfinderCache extends FileCache
{
    public function __construct($query, $page)
    {
        $this->lifetime = 3600 * 24 * 7; // one week
        $this->cache_dir = $this->PTH->cache . 'iconfinder/';
        
        $query = preg_replace('/(^[^a-z0-9]+|[^a-z0-9]+$)/i', '', $query);
        $query = preg_replace('/[^a-z0-9]+/i', '_', $query);
        
        $this->file = $this->cache_file = $this->cache_dir . $query . '-' . $page;
    }
}
?>