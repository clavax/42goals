<?php
import('base.controller.BaseApi');

class OAuthApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        
        $this->addRule(array(
            'requesttoken' => '(request_token): null',
            'accesstoken'  => '(access_token): null',
            'test'         => '(test): null',
        ));
    }
    
    public function handleGetRequestToken($query)
    {
        // check application credits and issues tokens
        
        try {
            $provider = new OAuthProvider();
            $provider->isRequestTokenEndpoint(true);
            $provider->consumerHandler(array('OAuthApi', 'lookupConsumer'));    
            $provider->timestampNonceHandler(array('OAuthApi', 'timestampNonceChecker'));
            $provider->setParam('query', null);
            $url = $this->URL->host . $this->URL->self;
            $provider->checkOAuthRequest($url, OAUTH_HTTP_METHOD_GET);
        } catch (OAuthException $e) {
            return OAuthProvider::reportProblem($e);
        }
        
        $time = floor((time() - mktime(0, 0, 0, 5, 29, 2010)) / (3600 * 24)); // days from May 29, 2010
        $rand1 = rand(1e+9, 1e+10);
        $rand2 = rand(1e+9, 1e+10);
        
        $token = array(
            'id' => md5($time . $rand1),
            'secret' => md5($rand2),
            'app' => $provider->consumer_id,
        );
        
        $Tokens = new PrimaryTable('request_tokens', 'id');
        $Tokens->insert($token);
        
        $data = array(
            'oauth_token' => $token['id'],
            'oauth_token_secret' => $token['secret'],
        );
        return http_build_query($data, null, '&');
    }
    
    public function handleGetAccessToken($query)
    {
        // exchange request token to access token if user has granted permissions
        
        try {
            $provider = new OAuthProvider();
            $provider->consumerHandler(array('OAuthApi', 'lookupConsumer'));
            $provider->timestampNonceHandler(array('OAuthApi', 'timestampNonceChecker'));
            $provider->tokenHandler(array('OAuthApi', 'requestTokenHandler'));
            $provider->setParam('query', null);
            $url = $this->URL->host . $this->URL->self;
            $provider->checkOAuthRequest($url, OAUTH_HTTP_METHOD_GET);
        } catch (OAuthException $e) {
            return OAuthProvider::reportProblem($e);
        }
        
        $time = floor((time() - mktime(0, 0, 0, 5, 29, 2010)) / (3600 * 24)); // days from May 29, 2010
        $rand1 = rand(1e+9, 1e+10);
        $rand2 = rand(1e+9, 1e+10);
        
        $token = array(
            'id'     => md5($time . $rand1),
            'secret' => md5($rand2),
            'app'    => $provider->consumer_id,
            'user'   => $provider->token_user,
        );
        
        $Tokens = new PrimaryTable('access_tokens', 'id');
        $Tokens->insert($token);
        
        $data = array(
            'oauth_token' => $token['id'],
            'oauth_token_secret' => $token['secret'],
        );

        return http_build_query($data, null, '&');
    }
    
    public function handlePostTest($query)
    {
        if (!self::isAuthorized()) {
            return 'error';
        }
        
        $data = array(
            'user' => $this->ENV->UID,
        );
        return http_build_query($data, null, '&');
    }
    
    public static function isAuthorized($params = array())
    {
        $authorized = false;
        try {
            $provider = new OAuthProvider();
            $provider->consumerHandler(array('OAuthApi', 'lookupConsumer'));
            $provider->timestampNonceHandler(array('OAuthApi', 'timestampNonceChecker'));
            $provider->tokenHandler(array('OAuthApi', 'accessTokenHandler'));
            $provider->setParam('query', null);
            foreach ($params as $key => $value) {
                $provider->setParam($key, $value);
            }
            
            $Conf = Framework::get('Conf');
            $url = $Conf->URL->host . $Conf->URL->self;
            $provider->checkOAuthRequest($url);
            
            $authorized = true;
            $Conf->ENV->UID = $provider->token_user;
        } catch (OAuthException $e) {
            return OAuthProvider::reportProblem($e);
        }
        return $authorized; 
    }
    
    public static function lookupConsumer($provider)
    {
        import('model.Apps');
        $Apps = new AppsModel;
        
        $app = $Apps->select(array('id', 'secret'), SQL::quote('appkey = ?', $provider->consumer_key), null, 1);
        $provider->consumer_secret = $app['secret'];
        $provider->consumer_id = $app['id'];
        
        return OAUTH_OK;
    }
    
    public static function timestampNonceChecker($provider)
    {
        if ($provider->nonce == 'bad') {
            return OAUTH_BAD_NONCE;
        } else if ($provider->timestamp == 0) {
            return OAUTH_BAD_TIMESTAMP;
        }
        return OAUTH_OK;
    }
    
    public static function requestTokenHandler($provider)
    {
        $Tokens = new PrimaryTable('request_tokens', 'id');
        $token = $Tokens->select(array('secret', 'user', 'app'), SQL::quote('id = ?', $provider->token), null, 1);
        
        $provider->token_secret = $token['secret'];
        $provider->token_user   = $token['user'];
        $provider->consumer_id  = $token['app'];
        
        return OAUTH_OK;
    }
    
    public static function accessTokenHandler($provider)
    {
        $Tokens = new PrimaryTable('access_tokens', 'id');
        $token = $Tokens->select(array('secret', 'user', 'app'), SQL::quote('id = ?', $provider->token), null, 1);
        
        $provider->token_secret = $token['secret'];
        $provider->token_user   = $token['user'];
        $provider->consumer_id  = $token['app'];
        
        return OAUTH_OK;
    }
}
?>