<?php
import('base.controller.BaseApi');

class ShareApi extends BaseApi
{
    public function handleGetDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        
        // check owner
        $goal = array_get($request, 'goal');
        if ($Goals->view($goal, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Shared = new DataTable('shared');
        
        // gen unique id
        $time = floor((time() - mktime(0, 0, 0, 5, 29, 2010)) / (3600 * 24)); // days from May 29, 2010
        $rand = rand(1e+9, 1e+10);
        $uid = self::dec62($time) . self::dec62($rand);

        $datastr = array_get($request, 'data');
        
        $data = array(
            'id' => $uid,
            'user' => $this->ENV->UID,
            'goal' => $goal,
            'data' => $datastr
        );
        
        if ($Shared->insert($data) === false) {
            return $this->respondOk(array('error' => 'error'));
        }
        
        $response = array(
            'item' => array(
                'uid' => $uid, 
                'data' => $datastr
            )
        );
        
        $post_to = array_get($request, 'post_to');
        if ($post_to == 'facebook') {
            $response['facebook'] = $this->sendToFacebook($uid, $datastr);
            if (!$response['facebook']) {
                $response['facebook_login'] = $this->facebook_login;
            }
        }
        
        return $this->respondOk($response);
    }
    
    private static function dec62($num, $base = 62, $index = false) 
    {
        if (!$base) {
            $base = strlen($index);
        } else if (!$index) {
            $index = substr('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 0, $base);
        }
        $out = '';
        for ($t = floor(log10($num) / log10($base)); $t >= 0; $t --) {
            $a = floor($num / pow($base, $t));
            $out = $out . substr($index, $a, 1);
            $num = $num - ($a * pow($base, $t));
        }
        return $out;
    }
    
    private function sendToFacebook($uid, $image)
    {
        import('3dparty.facebook');
        
        $facebook = new Facebook(array(
          'appId'  => '103692436343171',
          'secret' => 'ff6b997f73cffb89c5b5a733db7275ca',
          'cookie' => true, // enable optional cookie support
        ));
        
        $this->facebook_login = $facebook->getLoginUrl(array('req_perms' => 'publish_stream'));
        
        $session = $facebook->getSession();

        if ($session) {
            try {
                $params = array(
                    // 'message' => 'Test message from 42goals',
                    'picture' => $image,
                    'link' => $this->URL->host . $this->URL->home . 'p/' . $uid,
                    // 'name' => 'My 42goals graph',
                    // 'description' => '...'
                );
                $this->T->feed = $facebook->api('/me/feed', 'POST', $params);
            } catch (FacebookApiException $e) {
                $this->Error->log(describe($e));
                return false;
            }
        } else {
            return false;
        }
        
        return true;
    }
}
?>