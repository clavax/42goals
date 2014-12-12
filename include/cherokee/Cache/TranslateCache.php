<?php
import('cherokee.Cache.FileCache');

class TranslateCache extends FileCache
{
    public function __construct($query)
    {
        $this->lifetime = 3600 * 24 * 7; // one week
        $this->cache_dir = $this->PTH->cache . 'translate/';
        
        $query = base64_encode($query);
        $query = str_replace('/', '_', $query);
        
        $this->file = $this->cache_file = $this->cache_dir . $query;
    }
}
?>