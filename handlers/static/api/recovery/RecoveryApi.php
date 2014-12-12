<?php
import('base.controller.BaseApi');
import('static.system.captcha.Captcha');
import('model.Users');
import('lib.email');
import('lib.validate');

class RecoveryApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(
            array(
                'confirmation' => '([a-f0-9]{32}): code'
            )
        );
    }
    
    public function handlePostDefault($request)
    {
        $Users = new UsersModel;
        
        $data = array_get($request, 'data');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('registration');

        $errors = false;
        if (Access::loggedIn()) {
            $user = $this->ENV->user;
            $user['id'] = $this->ENV->UID;
        } else {
            if (!isset($data['captcha']) || !Captcha::checkId($data['captcha'])) {
                Captcha::genId();
                return $this->respondOk(array('error' => array('captcha' => $this->LNG->Error_wrong_captcha)));
            }
            
            if (!isset($data['email']) || empty($data['email'])) {
                return $this->respondOk(array('error' => array('email' => $this->LNG->Error_empty_email)));
            }
            
            // get user
            $user = $Users->select(array('id', 'status', 'email', 'name'), SQL::quote('email ' . SQL::ILIKE . ' ?', (string) $data['email']), null, 1);
            if (!$user) {
                return $this->respondOk(array('error' => array('email' => $this->LNG->Error_wrong_email)));
            } else if ($user['status'] != 'active') {
                return $this->respondOk(array('error' => array('email' => $this->LNG->Error_inactive_email)));
            }
        }
        
        $code = UsersModel::genConfirmationCode();
        if (!$Users->edit($user['id'], array('password_recovery' => $code))) {
            return $this->respondOk(array('error' => 'error'));
        }
        
        // send email confirmation
        $this->T->code = $code;
        $this->T->name = $user['name'];
        $message = $this->T->return_template('this.email_' . $this->ENV->language);
        
        $to      = $user['email'];
        $from    = "{$this->CNF->site->admin} <{$this->CNF->site->email}>";
        $subject = $this->LNG->Recovery;
        
        $headers = "From: $from\n"
                 . "Content-Type: text/plain; charset=utf-8";
        email::send($to, $subject, $message, $headers);
        
        Captcha::genId();

        return $this->respondOk(array('item' => $user));
    }
    

    public function handleGetConfirmation()
    {
        $Users = new UsersModel;
        $this->Conf->loadLanguage('recovery');
        
        // activate user account
        $pass = uniqid();
        
        $id = $Users->select('id', SQL::quote('% = ?', 'password_recovery', $this->ENV->code), null, 1);
        if (!$id) {
            header('Location: ' . $this->URL->home);
            return false;
        }

        if (!$Users->edit($id, array('password_recovery' => '', 'password' => $pass))) {
            $this->status = HttpRouter::STATUS_APP_ERROR;
            return false;
        }
        
        $user = $Users->view($id, array('email', 'name'));

        // send email confirmation
        $this->T->pass = $pass;
        $this->T->name = $user['name'];
        $message = $this->T->return_template('this.email_pass_' . $this->ENV->language);
        
        $to      = $user['email'];
        $from    = "{$this->CNF->site->admin} <{$this->CNF->site->email}>";
        $subject = $this->LNG->Recovery;
        
        $headers = "From: $from\n"
                 . "Content-Type: text/plain; charset=utf-8";
        email::send($to, $subject, $message, $headers);
        
        $this->Session->UID = $id;
        Access::setEnv(true);
        header('Location: ' . $this->URL->home);
        return true;
    }
}
?>