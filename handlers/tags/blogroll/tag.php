<?php
import('base.controller.BaseTag');

class BlogrollTag extends BaseTag
{
    public function handle(array $params = array())
    {
        //return $this->T->return('tag.blogroll');
        
        $this->Conf->loadLanguage('site');

        $cache_key = 'blog-posts';
        $items ="";
        $posts = $this->Memcache->get($cache_key);
        if (!$posts) {
            $url = 'http://blog.42goals.com/rss';
            $content = file::get_remote($url);
######################################################
error_reporting(E_ALL);
            $url = 'http://blog.42goals.com/rss';

            $referer='';
        if (function_exists('curl_init')) {
            $c = curl_init();
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_URL, $url);
            if ($referer) {
                curl_setopt($c, CURLOPT_REFERER, $referer);
            }

            $contents = curl_exec($c);
            curl_close($c);
        } else {

            $url = str_replace(' ', '%20', $url);
            $contents = file_get_contents($url);
        }
 file_get_contents($url);
 $content = $contents;
//        var_dump($contents);

######################################################            
//        echo "<pre>";
//        print_r($content);
//        die;

            $xml = new DOMDocument();
	    if($content)
	    {
                $xml->loadXML($content);            
                $items = $xml->getElementsByTagName('item');
                if (!$items->length) {
                    return '';
                }
            }  
//            var_dump($items);
            $posts = array();
            foreach ($items as $i => $item) {
//                            echo "<br>".$i." => ".$item;
//                            echo $item->getElementsByTagName('title')->item(0)->nodeValue;
                            }
            $max_num = array_get($params, 'count', 10);
//		if(isset($items) && is_array($items))
//		{
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
//		}
        }
//        var_dump($posts);
        $this->T->posts = $posts;
        
        return $this->T->return('tag.blogroll');
    }
}