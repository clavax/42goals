<?php
import('base.controller.BasePage');

class TestPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array('facebook' => '(facebook): null'));
        $this->addRule(array('googlehealth' => '(googlehealth): null'));
        $this->addRule(array('googleapi' => '(googleapi): null'));
        $this->addRule(array('protovis' => '(protovis): null'));
        $this->addRule(array('svgweb' => '(svgweb): null'));
    }
    
    public function handleDefault()
    {
        return $this->T->return('this.interface');
    }
    
    public function handleGoogleHealth($request)
    {
        header('Content-Type: text/plain');
        
        $key = '42goals.com';
        $secret = 'AKVKypmaetnJL8tlprH5MGCN';
        
        try {
            $oauth = new OAuth($key, $secret, OAUTH_SIG_METHOD_HMACSHA1);
            $oauth->disableSSLChecks();
            $oauth->enableDebug();
            
            if (isset($request['clear'])) {
                unset($this->Session->oauth_token, $this->Session->oauth_secret);
            }
            
            if (isset($request['oauth_token'])) {
                $oauth->setToken($this->Session->oauth_token, $this->Session->oauth_secret);
                $access_token_info = $oauth->getAccessToken('http://www.google.com/accounts/OAuthGetAccessToken');
                
                $this->Session->oauth_token  = $access_token_info['oauth_token'];
                $this->Session->oauth_secret = $access_token_info['oauth_token_secret'];
            }
            
            if (!isset($this->Session->oauth_token)) {
                $request_token_info = $oauth->getRequestToken('http://www.google.com/accounts/OAuthGetRequestToken?scope=' . urlencode('https://www.google.com/h9/feeds/'));
                $this->Session->oauth_token  = $request_token_info['oauth_token'];
                $this->Session->oauth_secret = $request_token_info['oauth_token_secret'];
                header('Location: https://h9.google.com/h9/oauth?oauth_callback=' . urlencode('http://chains.my/test/googlehealth/') . '&secure=0&permission=1&oauth_token=' . $request_token_info['oauth_token']);
                return true;
            }
            
            $oauth->setToken($this->Session->oauth_token, $this->Session->oauth_secret);
            
            $oauth->fetch('https://www.google.com/h9/feeds/profile/default', null, OAUTH_HTTP_METHOD_GET, array('GData-Version' => '2'));
            echo $oauth->getLastResponse();
            
            return true;
            
        } catch(OAuthException $E) {
              print_r($E);
        }
    }
    
    public function handleFacebook()
    {
        import('3dparty.facebook');
        
        $facebook = new Facebook(array(
          'appId'  => '103692436343171',
          'secret' => 'ff6b997f73cffb89c5b5a733db7275ca',
          'cookie' => true, // enable optional cookie support
        ));
        
        $session = $facebook->getSession();

        $me = null;
        // Session based API call.
        if ($session) {
            try {
                $uid = $facebook->getUser();
                $me = $facebook->api('/me');
                $this->T->uid = $uid;
                $this->T->app_id = $facebook->getAppId();
                if (isset($_GET['msg'])) {
                    $params = array(
                        'message' => 'Test message from 42goals',
                        'picture' => 'http://bit.ly/dkeaa8',
                        'link' => 'http://42goals.com',
                        'name' => 'My 42goals graph',
                        'description' => '...'
                    );
                    $this->T->feed = $facebook->api('/me/feed', 'POST', $params);
                }
            } catch (FacebookApiException $e) {
                return describe($e);
            }
        }
        $this->T->session = $session;
        $this->T->me = $me;
        
        // login or logout url will be needed depending on current user state.
        if ($me) {
            $this->T->logoutUrl = $facebook->getLogoutUrl();
        } else {
            $this->T->loginUrl = $facebook->getLoginUrl(array('req_perms' => 'publish_stream'));
        }
        
        // This call will always work since we are fetching public data.
        $this->T->naitik = $facebook->api('/naitik');

        return $this->T->return('this.fb');
    }
    
    public function handleGoogleApi() {
        return $this->T->return('this.google-api~html');
    }
    
    public function handleProtovis() {
        return $this->T->return('this.protovis~html');
    }
    
    public function handleSvgweb() {
        return $this->T->return('this.svgweb');
    }
}
?>