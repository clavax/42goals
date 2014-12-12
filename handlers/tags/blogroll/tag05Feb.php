<?php
import('base.controller.BaseTag');

class BlogrollTag extends BaseTag
{
    public function handle(array $params = array())
    {
            $url = 'http://blog.42goals.com/rss';
            $content = file::get_remote($url);

       
//        return $this->T->return('tag.blogroll');
        
        $this->Conf->loadLanguage('site');

        $cache_key = 'blog-posts';
        
//        $posts = $this->Memcache->get($cache_key);
//        if (!$posts) {

        
            $xml = new DOMDocument();
            $xml->loadXML($content);
            
            $items = $xml->getElementsByTagName('item');
            if (!$items->length) {
                return '';
            }
            
            $posts = array();
            $max_num = array_get($params, 'count', 10);
            foreach ($items as $i => $item) {
                if ($i >= $max_num) {
                    break;
                }
                $posts[] = array (
                    'title' => $item->getElementsByTagName('title')->item(0)->nodeValue,
                    'link' => $item->getElementsByTagName('link')->item(0)->nodeValue,
                    'time' => date('H:i M d', strtotime($item->getElementsByTagName('pubDate')->item(0)->nodeValue))
                );
            }
            $this->Memcache->set($cache_key, $posts, MEMCACHE_COMPRESSED, 3600);
//        }
        
        $this->T->posts = $posts;
        
        return $this->T->return('tag.blogroll');
    }
}
