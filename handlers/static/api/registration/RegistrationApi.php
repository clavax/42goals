<?php
import('base.controller.BaseApi');
import('static.system.captcha.Captcha');
import('model.RegistrationUsers');
import('lib.email');
import('lib.validate');

class RegistrationApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(
            array(
                'confirmation' => '([a-f0-9]{32}): code',
            )
        );
    }
    
    // process registration
    public function handlePostDefault($request)
    {
        $Users = new RegistrationUsersModel;
        
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('registration');
                
        // list of allowed fields
        $allowed = array('name', 'email', 'login', 'password', 'password2', 'captcha');

        $data = array();
        foreach ($allowed as $field) {
            if (!isset($request['data'][$field])) {
                continue;
            }
            $data[$field] = $request['data'][$field];
        }
                                   
        // add user
        $data['registered'] = date('Y-m-d H:i:s');
        $data['email_confirmation'] = UsersModel::genConfirmationCode();
        if (($id = $Users->add($data)) === false) {
            $errors = $Users->errors;
            
            foreach ($errors as $field => &$error) {
                $error = $this->LNG->get("Error_{$error}_{$field}", "Error_{$error}_{$field}");
            }
            
            return $this->respondOk(array('error' => $errors));
        }
		
        $data = $Users->view($id);
        
        // send email confirmation
        $this->T->data = $data;
        $this->T->code = $data['email_confirmation'];
        $this->T->premium = isset($request['premium']) ? '?premium' : '';
        $message = $this->T->return_template('this.email_' . $this->ENV->language);
        
        $to      = $data['email'];
        $from    = "{$this->CNF->site->admin} <{$this->CNF->site->email}>";
        $subject = $this->LNG->Activation;
        
        $headers = "From: $from\n"
                 . "Content-Type: text/plain; charset=utf-8";
        email::send($to, $subject, $message, $headers);
       Captcha::genId();
        
        unset($data['email_confirmation']);
        
		
        return $this->respondOk(array('item' => $data)); 
    }
    
    // resend email confirmation
    public function handlePutDefault($request)
    {
        $Users = new RegistrationUsersModel;
        
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('registration');

        $data = array_get($request, 'data');
        
        if (!isset($data['email']) || empty($data['email'])) {
            return $this->respondOk(array('error' => array('email' => $this->LNG->Error_empty_email)));
        }
        
        // get user
        $user = $Users->select(array('status', 'email', 'email_confirmation'), SQL::quote('email ' . SQL::ILIKE . ' ?', (string) $data['email']), null, 1);
        if (!$user) {
            return $this->respondOk(array('error' => array('email' => $this->LNG->Error_wrong_email)));
        } else if ($user['status'] == 'active') {
            return $this->respondOk(array('error' => array('email' => $this->LNG->Error_active_email)));
        }
        
        // send email confirmation
        $this->T->data = $user;
        $this->T->code = $user['email_confirmation'];
        $message = $this->T->return_template('this.email_' . $this->ENV->language);
        
        $to      = $user['email'];
        $from    = "{$this->CNF->site->admin} <{$this->CNF->site->email}>";
        $subject = $this->LNG->Activation;
        
        $headers = "From: $from\n"
                 . "Content-Type: text/plain; charset=utf-8";
        email::send($to, $subject, $message, $headers);
        
        unset($user['email_confirmation']);
        return $this->respondOk(array('item' => $user));
    }
    
    public function handleGetConfirmation(array $request = array())
    {
        $Users = new UsersModel;
        
        // activate user account
        $id = $Users->confirm($this->ENV->code, array('status' => 'active'));
        if ($id) {
            $this->Session->UID = $id;
            Access::setEnv(true);
            
            // add initial goals
            import('model.Goals');
            $Goals = new GoalsModel;
            
            $goals = $this->Conf->parse($this->PTH->config . 'init_' . $this->ENV->language . '.ini');
            foreach ($goals as $goal) {
                $goal['user'] = $id;
                $Goals->add($goal);
            }
            
            // create directory
            //file::mkdir($this->PTH->icons . $this->ENV->user->login, 0775);
			
			// AS we are saving uploaded files in single dir therefore no more folder need to create //
			
			//exec('mkdir -p '.$this->PTH->icons . $this->ENV->user->login);
			//exec('chmod -R 777 '.$this->PTH->icons . $this->ENV->user->login);
			
            if (isset($request['premium'])) {
                header('Location: ' . $this->URL->home . 'pay/');
            } else {
                header('Location: ' . $this->URL->home . 'goals/');
            }
        } else {
            header('Location: ' . $this->URL->home);
        }
        return true;
    }
}
?>