<?php
import('base.controller.BaseApi');
import('cherokee.Cache.IconfinderCache');
import('cherokee.Cache.TranslateCache');

class IconFinderApi extends BaseApi
{
    private $key = '0addf5df1b4df2a3aa87ae3a0d581d87';
    
    public function handleGetDefault(array $request = array())
    {
        $query = array_get($request, 'q');
        $page  = array_get($request, 'p', 0);
        
        // translate query
        $key = 'googletranslate_' . base64_encode($query);
        $translated = $this->Memcache->get($key);
        if (!$translated) {
            import('lib.google');
            if ($query == 'no') {
                $translated = 'no';
            } else {
                $translated = urldecode(google::translate($query, '', 'en'));
            }
            $this->Memcache->set($key, $translated, MEMCACHE_COMPRESSED, 3600); // 1 hour
        }
        
        $key = 'iconfinder_' . base64_encode($translated) . '_' . $page;
        $response = $this->Memcache->get($key);
        if (!$response) {
            $args = http_build_query(array(
                //'min'   => 0,
                //'max'   => 32,
                //'c'     => 50,
                'q'     => $query,
                'c'     => 10,
                'p'     => $page,
                'min'   => 1,
                'max'   => 32,
                'l'   => 0,
                'api_key' => $this->key,
            ), null, '&');
            
//die($args);

             $url = 'http://www.iconfinder.com/xml/search/?' . $args;
  
//            describe($url, 1);
//            $response = file::get_remote($url);
            $response = file_get_contents($url);
            $response = xml::cleanup($response);
            $this->Memcache->set($key, $response, MEMCACHE_COMPRESSED, 3600); // 1 hour
        }
        
        if (!strlen($response)) {
            $response = '<error>error</error>';
        }
        
        headers::content('text/xml');
        return $response;
    }
}
?>