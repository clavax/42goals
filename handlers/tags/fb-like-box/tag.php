<?php
import('base.controller.BaseTag');

class FbLikeBoxTag extends BaseTag
{
    public function handle(array $params = array())
    {
        import('cherokee.Cache.StaticFileCache');
        import('lib.json');
        
        $cache_key = 'facebook-page';
        $data = $this->Memcache->get($cache_key);
        if (!$data) {
            $key = 'e20d86eee87d7ca08564f555c473e779';
            $secret = '8ea66a58352584cf98cae0214ca50f84';
            
            // get access token
            $url = 'https://graph.facebook.com/oauth/access_token';
            $params = array(
                'grant_type' => 'client_credentials',
                'client_id'  => $key,
                'client_secret' => $secret
            );
            $content = file::get_remote($url . '?' . http_build_query($params, null, '&'));
            list($null, $access_token) = explode('=', $content, 2);
             
            // get page info
            $url = 'https://graph.facebook.com/42goals';
            $content = file::get_remote($url);
            $page = json::decode($content);

// facebook doesn't support this anymore            
//            // get page fans
//            $url = 'https://graph.facebook.com/' . $page->id . '/members/';
//            $params = array(
//                'limit' => 12,
//                'access_token' => $access_token
//            );
//            $content = file::get_remote($url . '?' . http_build_query($params, null, '&'));
//            $fans = json::decode($content);
            $fans = array();
            
            $data = array('page' => $page, 'fans' => $fans);
            if ($page) {
                $this->Memcache->set($cache_key, $data, null, 3600);
            }
        }
        $this->T->page = $data['page'];
        $this->T->fans = $data['fans'];
        
        return $this->T->return('tag.fb-like-box');
    }
}