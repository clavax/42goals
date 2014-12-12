<?php
import('base.controller.BaseTag');

class BlogEntryTag extends BaseTag
{
    public function handle(array $params = array())
    {
        import('cherokee.Cache.StaticFileCache');
        $this->Conf->loadLanguage('site');
        
        $Cache = new StaticFileCache($this->PTH->cache . 'blog/', 'blog.rss');
        $Cache->lifetime = 3600;
        if (!$Cache->status) {
            $url = 'http://blog.42goals.com/rss';
            $Cache->content = file::get_remote($url);
        }
        
        $xml = new DOMDocument();
        $xml->loadXML($Cache->content);
        
        $items = $xml->getElementsByTagName('item');
        if (!$items->length) {
            return '';
        }
        
        $this->T->title = $items->item(0)->getElementsByTagName('title')->item(0)->nodeValue;
        $this->T->url   = $items->item(0)->getElementsByTagName('link')->item(0)->nodeValue;
        return $this->T->return('tag.blog-entry');
    }
}