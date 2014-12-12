<?php
class Facebook extends Object
{
    const URL_REQUEST_TOKEN = 'https://api.twitter.com/oauth/request_token';
    const URL_ACCESS_TOKEN  = 'https://api.twitter.com/oauth/access_token';
    const URL_AUTHORIZE     = 'https://api.twitter.com/oauth/authorize';
    
    public function __construct($key, $secret) 
    {
        try {
            $this->oauth = new OAuth($key, $secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
            $this->oauth->disableSSLChecks();
            $this->oauth->enableDebug();
        } catch (Exception $e) {
            $this->Error->log(describe($e));
        }
        $this->connected = false;
        
        if (isset($_COOKIE['fb_token'])) {
            $this->Session->fb_token = $_COOKIE['fb_token'];
            $this->Session->fb_secret = $_COOKIE['fb_secret'];
        }
        if (isset($this->Session->fb_token)) {
            $this->setAccessToken($this->Session->fb_token, $this->Session->fb_secret);
        }
    }
    
    public function isConnected()
    {
        return $this->connected;
    }
    
    public function setAccessToken($token, $secret)
    {
        cookie::set('fb_token', $token, time() + 3600 * 24 * 30, '/', '');
        cookie::set('fb_secret', $secret, time() + 3600 * 24 * 30, '/', '');
                
        $this->oauth->setToken($token, $secret);
        $this->connected = true;
    }
    
    public function getAccessToken()
    {
        $this->oauth->setToken($this->Session->tw_request_token, $this->Session->tw_request_secret);
        $access_token_info = $this->oauth->getAccessToken(self::URL_ACCESS_TOKEN);
        $this->setAccessToken($access_token_info['oauth_token'], $access_token_info['oauth_token_secret']);
    }
    
    public function authorize($callback)
    {
        $request_token_info = $this->oauth->getRequestToken(self::URL_REQUEST_TOKEN  . '?oauth_callback=' . urlencode($callback));
        $this->Session->fb_request_token  = $request_token_info['oauth_token'];
        $this->Session->fb_request_secret = $request_token_info['oauth_token_secret'];
        headers::location(self::URL_AUTHORIZE . '?oauth_token=' . $request_token_info['oauth_token']);
        return true;
    }
    
    protected function fetch($url, $data = null, $method = OAUTH_HTTP_METHOD_GET)
    {
        $response = false;
        try {
            $this->oauth->fetch($url, $data, $method);
            $content = $this->oauth->getLastResponse();
            $response = json_decode($content);
        } catch (OAuthException $e) {
            $this->Error->log(describe($e));
        }
        return $response;
    }
    
    public function verifyCredentials()
    {
        return $this->fetch(self::URL_VERIFY, null, OAUTH_HTTP_METHOD_GET);
    }
    
    public function retweetedBy($tweet_id)
    {
        $url_retweeted_by = 'http://api.twitter.com/1/statuses/' . $tweet_id . '/retweeted_by.json';
        return $this->fetch($url_retweeted_by, null, OAUTH_HTTP_METHOD_GET);
    }
    
    public function retweets($tweet_id)
    {
        $url_retweets = 'http://api.twitter.com/1/statuses/retweets/' . $tweet_id . '.json';
        return $this->fetch($url_retweets, null, OAUTH_HTTP_METHOD_GET);
    }
    
    public function update($tweet_text)
    {
        $url_update = 'http://api.twitter.com/1/statuses/update.json?trim_user=true';
        $data = array('status' => $tweet_text);
        return $this->fetch($url_update, $data, OAUTH_HTTP_METHOD_POST);
    }
    
    public function retweet($tweet_id)
    {
        $url_retweet = 'http://api.twitter.com/1/statuses/retweet/' . $tweet_id . '.json';
        return $this->fetch($url_retweet, null, OAUTH_HTTP_METHOD_POST);
    }
    
    public function search($query, $since_id = null)
    {
        $url_update = 'http://search.twitter.com/search.json';
        $params['q'] = $query;
        $params['rpp'] = 100;
        if (isset($since_id)) {
            $params['since_id'] = $since_id;
        }
        return $this->fetch($url_update, $params, OAUTH_HTTP_METHOD_GET);
    }
    
    public function lookup($ids)
    {
        $url_lookup = 'http://api.twitter.com/1/users/lookup.json';
        $data = array('screen_name' => $ids);
        return $this->fetch($url_lookup, $data, OAUTH_HTTP_METHOD_GET);
    }
}