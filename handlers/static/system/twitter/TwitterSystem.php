<?php
import('base.controller.BasePage');

class TwitterSystem extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'connect' => '(connect): type',
            'disconnect' => '(disconnect): type'
        ));
    }
    
    public function handleGetConnect(array $request = array())
    {
        $callback = array_get($request, 'redirect');
        if (empty($callback)) {
            $callback = $this->URL->home;    
        }
        
        try {
            if (!$this->Twitter->isConnected()) {
                if (isset($request['oauth_token'])) {
                    $this->Twitter->getAccessToken();
                    headers::location($this->URL->self . '?redirect=' . $callback);
                    return true;
                } else {
                    return $this->Twitter->authorize($this->URL->host . $this->URL->self . '?redirect=' . $callback);
                }
            }
        } catch (OAuthException $E) {
            headers::content('text/plain');
            describe($E, 1);
            return true;
        }
        $data = $this->Twitter->verifyCredentials();
        if ($data) {
            Access::twitterConnect($data);
        }
        
        headers::location($callback);
        
        return true;
    }
    
    public function handleGetDisconnect(array $request = array())
    {
        cookie::remove('tw_token');
        cookie::remove('tw_secret');
        unset($this->Session->tw_token, $this->Session->tw_secret);        
    }
}
?>