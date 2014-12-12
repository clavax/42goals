<?php
import('model.Users');
import('lib.cookie');

class Access
{
    protected static $error = false;
    
    public static function login($login, $password, $remember = false)
    {
        $Users = new UsersModel;

        if (!$Users->existsWhere(SQL::quote('(login = ? or email = ?) and password = ? and status = ?', (string) $login, (string) $login, (string) UsersModel::hashPassword($password), 'active'))) {
            self::$error = true;
            return false;
        }
        $user = $Users->select(array('id', 'password'), SQL::quote('(login = ? or email = ?)', $login, $login), null, 1);

        $Session = Framework::get('Session');
        $Session->UID = $user['id'];
        if ($remember) {
            $this->rememberUser($user['id'], $user['password']);
        }

        self::setEnv(true);
        return true;
    }
    
    public static function rememberUser($id, $password = null)
    {
        if (!isset($password)) {
            $Users = new UsersModel;
            $password = $Users->view($id, 'password');
        }
        cookie::set('UID', $id, time() + 3600 * 24 * 30, '/', '');
        cookie::set('UP', md5($password), time() + 3600 * 24 * 30, '/', '');
    }
    
    public static function forgetUser()
    {
        cookie::remove('UID');
        cookie::remove('UP');
        unset($_COOKIE['UID'], $_COOKIE['UP']);
    }
        
    public static function loggedIn()
    {
        $Session = Framework::get('Session');
        $logged = false;
        if (isset($Session->UID) && !empty($Session->UID)) {
            $logged = true;
        } else {
            if (isset($_COOKIE['UID']) && isset($_COOKIE['UP'])) {
                $Users = new UsersModel;
                $logged = $Users->existsWhere(SQL::quote('id = ? and md5(password) = ?', $_COOKIE['UID'], (string) $_COOKIE['UP']));
                if ($logged) {
                    $Session->UID = $_COOKIE['UID'];
                }
            }
        }
        return $logged;
    }
        
    public static function isAdmin()
    {
        $Conf = Framework::get('Conf');
        $admin_id = $Conf->CNF->languages[$Conf->ENV->language]->admin;
        $admin_ids = array();
        foreach ($Conf->CNF->languages as $name => $lang) {
            $admin_ids[] = $lang['admin'];
        }
        return in_array($Conf->ENV->UID, $admin_ids);
    }

    public static function setEnv($logged = false)
    {
        $Conf = Framework::get('Conf');
        $Session = Framework::get('Session');
        
        if ($logged || self::loggedIn()) {
            $Conf->ENV->UID = isset($Session->UID) ? $Session->UID : (isset($_COOKIE['UID']) ? $_COOKIE['UID']: 0);
            $Conf->ENV->SID = $Session->getId();
            
            $Users = new UsersModel;
            $fields = array(
                'login',
                'name',
                'email',
                'new_email',
                'paid_till',
                'picture',
                'thumbnail',
                'location',
                'status',
                'url',
                'bio',
                'geo',
                'public',
                'receive_emails',
                'registered'
            );
            $user = $Users->view($Conf->ENV->UID, $fields);
	    $Conf->ENV->__set('paid_till', $user['paid_till']);	
	    $Conf->ENV->__set('registered', $user['registered']);			
            $paid_till = strtotime($user['paid_till']);
            $user['premium'] = strlen($user['paid_till']) > 0;
            $user['valid'] = $user['premium'] && strtotime($user['paid_till']) > time();
            $Conf->ENV->__set('user', $user);
 
        } else {
            $Conf->ENV->UID = 0;
        }
    }

    public static function getError()
    {
         return self::$error;
    }
    
    public static function logout()
    {
//        import('static.system.twitter.TwitterSystem');
//        $Twitter = new TwitterSystem();
//        $Twitter->handleGetDisconnect();
        
        $Session = Framework::get('Session');
        unset($Session->UID);
        $Session->destroy();
        Access::forgetUser();
    }
    
    public static function twitterConnect($data)
    {
        $Session = Framework::get('Session');
        
        // create a new user if neccessary
        $Users = new UsersModel;
        $user_id = $Users->select('id', SQL::quote('tw_id = ?', $data->id_str), null, 1);
        
        if (!$user_id) {
            $Conf = Framework::get('Conf');
            
            $user = array(
                'name'      => $data->name,
                'login'     => 'tw:' . $data->screen_name,
                'password'  => uniqid(),
                'location'  => $data->location,
                'url'       => 'http://twitter.com/' . $data->screen_name,
                'bio'       => $data->description,
                'tw_id'     => $data->id_str,
                'tw_token'  => $Session->tw_token,
                'tw_secret' => $Session->tw_secret,
                'status'    => 'twitter',
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
            $Session->UID = $user_id;
            self::setEnv(true);
            
            // create directory
            file::mkdir($Conf->PTH->icons . $Conf->ENV->user->login, 0775);
            
            // save picture
            $image = file::get_remote($data->profile_image_url);
            $upload_dir = $Conf->PTH->tmp . 'upload/';
            $tmp_name = time() . '_' . md5(uniqid('', true));
            file::put_contents($upload_dir . $tmp_name, $image);
            $real_name = parse_url($data->profile_image_url, PHP_URL_PATH);
            
            import('static.api.settings.SettingsApi');
            $Api = new SettingsApi;
            $Api->handlePostPicture(array('tmp_name' => $tmp_name, 'real_name' => $real_name));
        } else {
            $user = array(
                'tw_token' => $Session->tw_token,
                'tw_secret' => $Session->tw_secret,
            );
            $Users->edit($user_id, $user);
            
            $Session->UID = $user_id;
            self::setEnv(true);
        }
        
        // remember user
        self::rememberUser($user_id);
    }
}
?>
