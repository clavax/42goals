<?php
import('base.controller.BaseApi');
import('model.SettingsUsers');
import('lib.email');
import('lib.message');
import('lib.validate');

class SettingsApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(
            array(
                'confirmation' => '((?:(?:email|jabber|icq|msn|yahoo)-)?[a-f0-9]{32}): code',
                'resend' => '((?:email|jabber|icq|msn|yahoo|phone)): type',
                'picture' => '(picture): null',
                'info' => '(info): null',
            )
        );
    }

    public function handlePostDefault($request)
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Users = new SettingsUsersModel;
        
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('settings');

        $user = $Users->view($this->ENV->UID, array('name', 'email'));

        // list of allowed fields
        $allowed = array('name', 'password', 'password1', 'password2', 'new_email');

        $data = array();
        foreach ($allowed as $field) {
            if (!isset($request['data'][$field])) {
                continue;
            }
            $data[$field] = $request['data'][$field];
        }
        $data['receive_emails'] = array_get($request['data'], 'receive_emails', 0);

        // add confirmation code for new contact information
        if (isset($data['new_email']) && !empty($data['new_email'])) {
            $data['email_confirmation'] = UsersModel::genConfirmationCode();
        }
		
		
        if ($Users->edit($this->ENV->UID, $data) === false) {
            $errors = $Users->errors;
            
            foreach ($errors as $field => &$error) {
                $key = "Error_{$error}_{$field}";
                $error = $this->LNG->has($key) ? $this->LNG->get($key) : $key;
            }
            
            return $this->respondOk(array('error' => $errors));
        }
		
        $this->sendConfirmations($data);
        
        unset($data['password'], $data['password1'], $data['password2']);

        return $this->respondOk(array('item' => $data)); 
    }
    
    public function handlePostInfo($request)
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Users = new UsersModel;
        
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('settings');

        // list of allowed fields
        $allowed = array('name', 'location', 'url', 'bio', 'public');

        $data = array();
        foreach ($allowed as $field) {
            if (!isset($request['data'][$field])) {
                continue;
            }
            $data[$field] = $request['data'][$field];
        }
        
        if (isset($data['location'])) {
            import('lib.yahoo');
            $geo = yahoo::placefinder($data['location']);
            if (isset($geo->ResultSet->Results[0]->latitude)) {
                $data['geo'] = $geo->ResultSet->Results[0]->latitude . ':' . $geo->ResultSet->Results[0]->longitude;
            }
        }
        
        $data['language'] = $this->ENV->language;

        if ($Users->edit($this->ENV->UID, $data) === false) {
            $errors = $Users->errors;
            
            foreach ($errors as $field => &$error) {
                $key = "Error_{$error}_{$field}";
                $error = $this->LNG->has($key) ? $this->LNG->get($key) : $key;
            }
            
            return $this->respondOk(array('error' => $errors));
        }

        return $this->respondOk(array('item' => $data)); 
    }

    public function handlePostPicture($request)
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        import('lib.image');
        
        $Users = new UsersModel;
        
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('settings');

        if (!isset($request['tmp_name'])) {
            return $this->respondOk(array('error' => 'no tmp name'));
        }
        
        $tmp_name = $request['tmp_name'];
        $real_name = $request['real_name'];
        
        // check image
        $ext = str::tolower(file::get_ext($real_name));
        if (!in_array($ext, array('gif', 'jpg', 'jpeg', 'png'))) {
            return $this->respondOk(array('error' => 'Wrong file type'));
        }
        
        $info = image::info($this->PTH->upload . $tmp_name);
        if ($info[0] > 3000 || $info[1] > 3000) {
            return $this->respondOk(array('error' => 'Image is too large'));
        }
        
        $new_name = $this->ENV->user->login . '_' . uniqid() . '.' . $ext;
        if (!file::rename($this->PTH->upload . $tmp_name, $this->PTH->userpics . $new_name)) {
            return $this->respondOk(array('error' => 'Cannot move uploaded file'));
        }
        
        $image = image::from_file($this->PTH->userpics . $new_name);
        $resized = image::resize($image, 180, 270);
        image::save($resized, $this->PTH->userpics . $new_name, $info[2]);
        
        $thumb = image::resize_cut($image, 50, 50);
        $thumb_name = $this->ENV->user->login . '_small' . uniqid() . '.' . $ext;
        image::save($thumb, $this->PTH->userpics . $thumb_name, $info[2]);
        
        $data = array('picture' => $new_name, 'thumbnail' => $thumb_name);
        
        $old = $Users->view($this->ENV->UID, array('picture', 'thumbnail'));

        if ($Users->edit($this->ENV->UID, $data) === false) {
            $errors = $Users->errors;
            
            foreach ($errors as $field => &$error) {
                $key = "Error_{$error}_{$field}";
                $error = $this->LNG->has($key) ? $this->LNG->get($key) : $key;
            }
            
            return $this->respondOk(array('error' => $errors));
        }
        
        if ($old['picture']) {
            if (file_exists($this->PTH->userpics . $old['picture'])) {
                file::delete($this->PTH->userpics . $old['picture']);
            }
            if (file_exists($this->PTH->userpics . $old['thumbnail'])) {
                file::delete($this->PTH->userpics . $old['thumbnail']);
            }
        }

        return $this->respondOk(array('item' => $data));
    }

    public function handleDeleteDefault($request)
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('settings');
        
        $Users = new SettingsUsersModel;
        if ($Users->deleteAccount(array_get($request, 'password')) === false) {
            $errors = $Users->errors;
            
            foreach ($errors as $field => &$error) {
                $key = "Error_{$error}_{$field}";
                $error = $this->LNG->has($key) ? $this->LNG->get($key) : $key;
            }
            
            return $this->respondOk(array('error' => $errors));
        }

        // delete goals data
        $Goals = new DataTable('goals');
        $goals = $Goals->select('id', SQL::quote('user = ?', $this->ENV->UID));
        $Data = new DataTable('data');
        $Data->delete_where(SQL::quote('goal in (?)', $goals));
        
        // delete goals
        $Goals->delete_where(SQL::quote('user = ?', $this->ENV->UID));
        
        // delete icons
        $Icons = new DataTable('icons');
        $Icons->delete_where(SQL::quote('user = ?', $this->ENV->UID));
		
		// lets log activity	
		$request['login'] = $this->ENV->user->login;
		$request['date'] = date('d-m-Y h:i:s');
		$request['path'] = $this->PTH->icons.$this->ENV->user->login;
		ob_start();
		print "<pre>";
		print_r($request);
		$content = ob_get_contents();
		$fp = fopen($this->PTH->userpics.'data.txt', 'a+');
		fwrite($fp, $content);
		fclose($fp);
		ob_end_clean();
		
		// Commented by Kanhaiya dated 5 feb  2013 we doubt code below is responsible to delete dirs from icons dir. 
		// Untill we get a solution 
        //file::rmdir($this->PTH->icons . $this->ENV->user->login);
        Access::logout();
        
        return $this->respondOk(array('item' => 'ok'));
    }    
    
    protected function sendConfirmations($data)
    {
        $this->Conf->loadLanguage('settings');
        
        // prepare email
        if (isset($data['email_confirmation'])) {
            $type       = 'email';
            $code       = $data['email_confirmation'];
            $email_to   = $data['new_email'];
            $subject    = $this->LNG->Email_change;
            $this->sendEmailConfirmation($type, $code, $email_to, $subject, $data);
        }
        
        // prepare Jabber
        if (isset($data['jabber_confirmation'])) {
            $type       = 'jabber';
            $code       = $data['jabber_confirmation'];
            $email_to   = $data['new_jabber'];
            $im_to      = $data['new_jabber'];
            $subject    = $this->LNG->Jabber_change;
            $this->sendEmailConfirmation($type, $code, $email_to, $subject, $data);
            $this->sendImConfirmation($type, $code, $im_to);
        }
        
        // ICQ
        if (isset($data['icq_confirmation'])) {
            $send_email = true;
            $type       = 'icq';
            $code       = $data['icq_confirmation'];
            $im_to      = $data['new_icq'];
            $this->sendImConfirmation($type, $code, $im_to);
        }
        
        // MSN (Live) messenger
        if (isset($data['msn_confirmation'])) {
            $type       = 'msn';
            $code       = $data['msn_confirmation'];
            $email_to   = $data['new_msn'];
            $im_to      = $data['new_msn'];
            $subject    = $this->LNG->Msn_change;
            $this->sendEmailConfirmation($type, $code, $email_to, $subject, $data);
            $this->sendImConfirmation($type, $code, $im_to);
        }
        
        // Yahoo messenger
        if (isset($data['yahoo_confirmation'])) {
            $type       = 'yahoo';
            $code       = $data['yahoo_confirmation'];
            $email_to   = $data['new_yahoo'] . '@' . 'yahoo.com';
            $im_to      = $data['new_yahoo'];
            $subject    = $this->LNG->Yahoo_change;
            $this->sendEmailConfirmation($type, $code, $email_to, $subject, $data);
            $this->sendImConfirmation($type, $code, $im_to);
        }
        
        // phone number
        if (isset($data['phone_confirmation'])) {
            $this->T->code = $data['phone_confirmation'];
            $message = $this->T->return_template('this.sms_' . $this->ENV->language);
            message::sms($data['new_phone'], $message);
        }
    }
    
    protected function sendImConfirmation($type, $code, $recepient)
    {
        $this->T->type = $type;
        $this->T->code = $code;
        
        $message = $this->T->return_template('this.msg_' . $this->ENV->language);

        switch ($type) {
        case 'jabber':
            $success = message::jabber($recepient, $message);
            break;
            
        case 'icq':
            $success = message::icq($recepient, $message);
            break;
            
        case 'msn':
            $success = message::msn($recepient, $message);
            break;
            
        case 'yahoo':
            $success = message::yahoo($recepient, $message);
            break;
        }
             
        return $success;
    }
    
    protected function sendEmailConfirmation($type, $code, $recepient, $subject, $data)
    {
        $this->T->type = $type;
        $this->T->code = $code;
        $this->T->data = $data;
        
        $message = $this->T->return_template('this.email_' . $this->ENV->language);
        $from    = "{$this->CNF->site->admin} <{$this->CNF->site->email}>";
        $headers = "From: $from\n"
                 . "MIME-Version: 1.0\n"
                 . "Content-Transfer-Encoding: 8bit\n"
                 . "Content-Type: text/plain; charset=utf-8";
        $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=\n";
        
        $success = message::email($recepient, $subject, $message, $headers);
        
        return $success;
    }
    
    public function handleGetResend()
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Users = new SettingsUsersModel;
        
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('settings');

        switch ($this->ENV->type) {
        case 'email':
            $new_field      = 'new_email';
            $confirm_field  = 'email_confirmation';
            break;
            
        case 'jabber':
            $new_field      = 'new_jabber';
            $confirm_field  = 'jabber_confirmation';
            break;
            
        case 'icq':
            $new_field      = 'new_icq';
            $confirm_field  = 'icq_confirmation';
            break;
            
        case 'msn':
            $new_field      = 'new_msn';
            $confirm_field  = 'msn_confirmation';
            break;
            
        case 'yahoo':
            $new_field      = 'new_yahoo';
            $confirm_field  = 'yahoo_confirmation';
            break;
            
        case 'phone':
            $new_field      = 'new_phone';
            $confirm_field  = 'phone_confirmation';
            break;
            
        default:
            return $this->respondOk(array('ok' => 'unknown type'));
        }

        $data = $Users->view($this->ENV->UID, array($new_field, $confirm_field));
        if (empty($data)) {
            return $this->respondOk(array('error' => 'no code'));
        }
        
        $this->sendConfirmations($data);
        
        return $this->respondOk(array('ok' => 'ok'));
    }

    public function handleGetConfirmation()
    {
        // get type and code from
        // eg. msn-1234567890abcdef[32]
        if (strpos($this->ENV->code, '-') !== false) {
            list($type, $code) = explode('-', $this->ENV->code, 2);
        } else {
            $type = 'email';
            $code = $this->ENV->code;
        }
        
        // set fields
        $condition = '';
        switch ($type) {
        case 'email':
            $new_field      = 'new_email';
            $old_field      = 'email';
            $confirm_field  = 'email_confirmation';
            break;
            
        case 'jabber':
            $new_field      = 'new_jabber';
            $old_field      = 'jabber';
            $confirm_field  = 'jabber_confirmation';
            break;
            
        case 'icq':
            $new_field      = 'new_icq';
            $old_field      = 'icq';
            $confirm_field  = 'icq_confirmation';
            break;
            
        case 'msn':
            $new_field      = 'new_msn';
            $old_field      = 'msn';
            $confirm_field  = 'msn_confirmation';
            break;
            
        case 'yahoo':
            $new_field      = 'new_yahoo';
            $old_field      = 'yahoo';
            $confirm_field  = 'yahoo_confirmation';
            break;
            
        default:
            $this->status = HttpRouter::STATUS_NOT_FOUND;
            return false;
        }
        
        // select user id and new contact info to set
        $Users = new UsersModel;
        $user = $Users->select(array('id', $new_field), SQL::quote('% = ?', $confirm_field, $code), 'id', 1);
        if (!$user) {
            $this->status = HttpRouter::STATUS_NOT_FOUND;
            return false;
        }
        
        // update user info
        $data = array(
            $old_field => $user[$new_field],
            $new_field => '',
            $confirm_field => SQL::NULL
        );
        
        $edited = $Users->edit($user['id'], $data);
        if ($edited === false) {
            $this->status = HttpRouter::STATUS_APP_ERROR;
            return false;
        }
        
        // authorize user
        $this->Session->UID = $user['id'];
        
        Access::setEnv(true);
        header('Location: ' . $this->URL->home . 'settings/');
        return true;
    }

    public function handleGetPhoneConfirmation()
    {
        list($type, $code) = explode('-', $this->ENV->code, 2);
        
        // select user id and new contact info to set
        $Users = new UsersModel;
        $user = $Users->select(array('id', 'new_phone'), SQL::quote('phone_confirmation = ?', $code), 'id', 1);
        if (!$user) {
            return $this->respondOk(array('error' => 'no code'));
        }
        
        // update user info
        $data = array(
            'phone' => $user['new_phone'],
            'new_phone' => '',
            'phone_confirmation' => SQL::NULL
        );
        
        $edited = $Users->edit($user['id'], $data);
        if (!$edited) {
            return $this->respondOk(array('error' => 'error'));
        }
        
        return $this->respondOk(array('ok' => 'ok'));
    }
}
?>