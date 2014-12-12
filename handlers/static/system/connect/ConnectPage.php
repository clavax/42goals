<?php
import('base.controller.BasePage');
class ConnectPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'facebook' => '(facebook): type',
            'twitter' => '(twitter): type'
        ));
    }
    
    public function handleGetTwitter(array $request = array())
    {
        if (isset($request['clear'])) {
            unset($this->Session->oauth_token, $this->Session->oauth_secret);
        }
        try {
            if (!$this->Twitter->isConnected()) {
                if (isset($request['oauth_token'])) {
                    $this->Twitter->getAccessToken();
                } else {
                    return $this->Twitter->authorize($this->URL->host . $this->URL->self);
                }
            }
        } catch (OAuthException $E) {
            headers::content('text/plain');
            print_r($E);
        }
        $data = $this->Twitter->verifyCredentials();
        return describe($data);
    }
    
    public function handleGetFacebook(array $request = array())
    {
        import('3dparty.facebook');
        
        $facebook = new Facebook(array(
          'appId'  => '103692436343171',
          'secret' => 'ff6b997f73cffb89c5b5a733db7275ca',
          'cookie' => true, // enable optional cookie support
        ));
        
        $session = $facebook->getSession();
        if ($session) {
            try {
                $me = $facebook->api('/me');
            } catch (Exception $e) {
                
            }
            if ($me) {
                $this->T->me = $me;
                $this->T->title = 'Facebook.login'; 
                $this->T->include('this.content');
                return $this->T->return('templates.blank');
            }
        }
        
        $url = $facebook->getLoginUrl(array('req_perms' => 'publish_stream'));
        
        //header('Location: ' . $url);
        return true;
    }
}
?>