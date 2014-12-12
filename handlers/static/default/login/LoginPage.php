<?php
import('base.controller.BasePage');

class LoginPage extends BasePage
{
    public function __construct()
    {
		
	  parent::__construct();
        $this->addRule(array(
            'snappcloud' => '(snappcloud)/(\d+)-([a-f0-9]{32}): null/order_id/hash'
        ));
		
    }
    
    public function handleDefault(array $request = array())
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        
        $redirect = array_get($request, 'redirect');
        $url = parse_url($redirect);
        if (isset($url['scheme'])) {
            $redirect = '';
        } else {
            $redirect = $url['path'];
        }
        
        $this->T->redirect = $redirect;
        $this->T->page_id = 'login-page';
        $this->T->page_title = $this->LNG->Title_403;
        $this->T->include('this.login', 'content');
        return $this->T->return('templates.inner');
    }
    
    public function handleMobile(array $request = array())
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        
        $redirect = array_get($request, 'redirect', $this->URL->this);
        $url = parse_url($redirect);
        if (isset($url['scheme'])) {
            $redirect = '';
        } else {
            $redirect = $url['path'];
        }
        
        $this->T->redirect = $redirect;
        
        $this->T->page_id = 'login-page';
        $this->T->page_title = $this->LNG->Title_403;
        $this->T->no_desktop_link = true;
        $this->T->include('this.mobile', 'content');
        return $this->T->return('templates.mobile');
    }
    
    public function handleSnappcloud(array $request = array())
    {
        $Snappcloud = new PrimaryTable('snappcloud', 'id');
        $user_id = $Snappcloud->select('user', SQL::quote('id = ? and hash = ?', $this->ENV->order_id, $this->ENV->hash), null, 1);
        
		//print $user_id = ($user_id)?$user_id:$this->ENV->UID;
       
	   
		if (!$user_id) {
            return 'Error';
        }
        
        $this->Session->UID = $user_id;
        Access::setEnv(true);
        Access::rememberUser($user_id);
        
        headers::location($this->URL->home . 'goals/');
        return true;
    }
}
?>