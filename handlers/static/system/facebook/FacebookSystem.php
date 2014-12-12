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
        
        if (isset($request['error'])) {
            headers::location($this->URL->home);
            return true;
        }
        
        if (!isset($request['code'])) {
            $url = 'https://www.facebook.com/dialog/oauth';
            $params = array(
                'client_id'     => $this->CNF->facebook->id,
                'redirect_uri'  => $this->URL->host . $this->URL->self . '?redirect=' . urlencode($callback),
                'scope'         => 'offline_access,user_about_me',
            );
            headers::location($url . '?' . http_build_query($params, null, '&'));
            return true;
        }
        
        $url = 'https://graph.facebook.com/oauth/access_token';
        $params = array(
            'client_id'         => $this->CNF->facebook->id,
            'client_secret'     => $this->CNF->facebook->secret,
            'code'              => $request['code'],
            'redirect_uri'      => $this->URL->host . $this->URL->self . '?redirect=' . urlencode($callback),
        );
        $content = file::get_remote($url . '?' . http_build_query($params, null, '&'));
        
        $args = array();
        parse_str($content, $args);
        $access_token = array_get($args, 'access_token');
        if (!$access_token) {
            describe($content, 1);
            describe($url . '?' . http_build_query($params, null, '&'), 1);
            return 'no access_token';
        }
        
        $graph_url = 'https://graph.facebook.com/me?' . http_build_query(array('access_token' => $access_token), null, '&');
        $user = json_decode(file::get_remote($graph_url));
        if (!isset($user->id)) {
            describe($user, 1);
            return 'no id';
        }
        
        // add or auth user
        $data = $user;
        $Users = new UsersModel;
        $user_id = $Users->select('id', SQL::quote('fb_id = ?', $data->id), null, 1);
        
        if (!$user_id) {
            $Conf = Framework::get('Conf');
            
            $login = strrchr($data->link, '/');
            $login = substr($login, 1);
            if (!$login) {
                $login = $data->id;
            }
            $login = preg_replace('/[^\w\d]+/', '_', $login);
            $user = array(
                'name'      => $data->name,
                'login'     => 'fb:' . $login,
                'password'  => uniqid(),
                'location'  => isset($data->location->name) ? $data->location->name : '',
                'url'       => $data->link,
                'bio'       => $data->about,
                'fb_id'     => $data->id,
                'fb_token'  => $access_token,
                'status'    => 'facebook',
                'public'    => 1,
                'language'  => $Conf->ENV->language,
                'registered' => date('Y-m-d H:i:s'),
            );
            
            // get location
            if ($user['location']) {
                import('lib.yahoo');
                $geo = yahoo::placefinder($user['location']);
                if (isset($geo->ResultSet->Results[0]->latitude)) {
                    $user['geo'] = $geo->ResultSet->Results[0]->latitude . ':' . $geo->ResultSet->Results[0]->longitude;
                }
            }
            
            // add user
            $UsersTable = new PrimaryTable('users', 'id');
            $user_id = $UsersTable->insert($user);
            
            if (!$user_id) {
                return;
            }
            
            // set session
            $this->Session->UID = $user_id;
            Access::setEnv(true);
            
            // create directory
            file::mkdir($this->PTH->icons . $this->ENV->user->login, 0775);
            
            // save picture
            $params = array('access_token' => $access_token, 'type' => 'large');
            $picture_url = 'https://graph.facebook.com/me/picture?' . http_build_query($params, null, '&');
            $headers = headers::get($picture_url);
            $parsed = headers::parse($headers);
            if (isset($parsed['Location'])) {
                $this->Error->log($parsed['Location']);
                $image = file::get_remote($parsed['Location']);
                $upload_dir = $Conf->PTH->tmp . 'upload/';
                $tmp_name = time() . '_' . md5(uniqid('', true));
                file::put_contents($upload_dir . $tmp_name, $image);
                $real_name = 'test.jpg';
                
                import('static.api.settings.SettingsApi');
                $Api = new SettingsApi;
                $Api->handlePostPicture(array('tmp_name' => $tmp_name, 'real_name' => $real_name));
            }
        } else {
            $user = array(
                'fb_token' => $access_token,
            );
            $Users->edit($user_id, $user);
            
            $this->Session->UID = $user_id;
            Access::setEnv(true);
        }
        
        // remember user
        Access::rememberUser($user_id);
        
        headers::location($callback);
        return true;
    }
}