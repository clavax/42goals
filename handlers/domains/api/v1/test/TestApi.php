<?php
import('base.controller.BaseApi');

class TestApi extends BaseApi
{
    public function handleGetDefault(array $request = array())
    {
        header('Content-Type: text/plain');
        
//        $key = '8448bb42f2ec101c3cf48ed9c1b374b2';
//        $secret = '5a5ad36f15284cade5b1cdf50d58ce6f';
        
        $key = '3c97d4213fd883e4c96efbaced1f6eba';
        $secret = 'cffcdb43595f10892564f382f91a5768';
        
        try {
            $this->Error->log($this->ENV->host);
            
            $oauth = new OAuth($key, $secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
            $oauth->disableSSLChecks();
            $oauth->enableDebug();
            
            if (isset($request['clear'])) {
                unset($this->Session->oauth_token, $this->Session->oauth_secret);
            }
            
            if (isset($request['oauth_token'])) {
                $oauth->setToken($this->Session->oauth_token, $this->Session->oauth_secret);
                $access_token_info = $oauth->getAccessToken("http://api.{$this->ENV->host}/v1/oauth/access_token/");
                
                $this->Session->oauth_token  = $access_token_info['oauth_token'];
                $this->Session->oauth_secret = $access_token_info['oauth_token_secret'];
            }
            
            if (!isset($this->Session->oauth_token)) {
                $request_token_info = $oauth->getRequestToken("http://api.{$this->ENV->host}/v1/oauth/request_token/");
                $this->Session->oauth_token  = $request_token_info['oauth_token'];
                $this->Session->oauth_secret = $request_token_info['oauth_token_secret'];
                header("Location: http://{$this->ENV->host}/settings/authorize/" . $request_token_info['oauth_token'] . '/');
                return true;
            }
            
            $oauth->setToken($this->Session->oauth_token, $this->Session->oauth_secret);
            
//            $oauth->fetch("http://api.{$this->ENV->host}/v1/goals/", null, OAUTH_HTTP_METHOD_GET, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";
            
//            $oauth->fetch('http://api.chains.my/v1/goals/37/', null, OAUTH_HTTP_METHOD_GET, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";
            
//            $oauth->fetch('http://api.chains.my/v1/goals/', array('title' => 'test', 'type' => 'test type', 'text' => 'test text'), OAUTH_HTTP_METHOD_POST, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";
            
//            $oauth->fetch('http://api.chains.my/v1/goals/56/', array('title' => 'test2', 'type' => 'test type2', 'text' => 'test text2'), OAUTH_HTTP_METHOD_PUT, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";
            
//            $oauth->fetch('http://api.chains.my/v1/goals/56/', null, OAUTH_HTTP_METHOD_DELETE, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";
            
//            $oauth->fetch('http://api.chains.my/v1/icons/', null, OAUTH_HTTP_METHOD_GET, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";

//            $image = base64_encode(file::get_contents($this->PTH->icons . 'loading.png'));
//            $oauth->fetch('http://api.chains.my/v1/icons/', array('image' => $image, 'name' => 'loading.png'), OAUTH_HTTP_METHOD_POST, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";

//            $oauth->fetch('http://api.chains.my/v1/icons/44/', null, OAUTH_HTTP_METHOD_DELETE, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";
            
//            $oauth->fetch('http://api.chains.my/v1/icons/38/', null, OAUTH_HTTP_METHOD_GET, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";
            
//            $oauth->fetch('http://api.chains.my/v1/data/54/1aug10/', array('value' => 1), OAUTH_HTTP_METHOD_POST, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse() . "\n";
            
//            $oauth->fetch('http://api.chains.my/v1/data/1aug10/', null, OAUTH_HTTP_METHOD_GET, array('Accept' => 'text/xml'));
//            echo $oauth->getLastResponse();
            
            $oauth->fetch("http://api.{$this->ENV->host}/v1/me/", null, OAUTH_HTTP_METHOD_GET, array('Accept' => 'text/xml'));
            echo $oauth->getLastResponse();
            
            return true;
            
        } catch(OAuthException $E) {
              print_r($E);
        }
    }
}
?>