<?php
import('base.controller.BaseTag');

class TwitterTag extends BaseTag
{
    public function handle(array $params = array())
    {
		return false;
	   import('cherokee.Cache.StaticFileCache');
        $this->Conf->loadLanguage('site');
        
        $cache_key = "twitter-timeline";
        $items = $this->Memcache->get($cache_key);
        if (!$items) {
            $url = 'https://api.twitter.com/1/statuses/user_timeline.xml?screen_name=42goals';
            $content = file::get_remote($url);
        
            $xml = new DOMDocument();
            $xml->loadXML($content);
            
            $statuses = $xml->getElementsByTagName('status');
            if (!$statuses->length) {
                return '';
            }
            
            $items = array();
            $max_num = array_get($params, 'count', 0);
            $n = 1;
            foreach ($statuses as $i => $status) {
                if ($n > $max_num) {
                    break;
                }
                $text = $status->getElementsByTagName('text')->item(0)->nodeValue;
                if ($text[0] == '@') { // direct reply
                    continue;
                }
                $items[] = array(
                    'text' => $text,
                    'time' => date('H:i M d', strtotime($status->getElementsByTagName('created_at')->item(0)->nodeValue))
                );
                $n ++;
            }
            $this->Memcache->set($cache_key, $items, MEMCACHE_COMPRESSED, 3600);
        }
        
        $this->T->statuses = $items;
        return $this->T->return('tag.twitter');
    }
    
    public static function urlify($str)
    {
        $str = preg_replace('/(http:\/\/[^\s\)]+)/im', '<a href="$1">$1</a>', $str);
        $str = preg_replace('/@([^\s\)]+)/im', '@<a href="http://twitter.com/$1">$1</a>', $str);
        return $str;
    }
}
