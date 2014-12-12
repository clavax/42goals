<?php
import('base.controller.BasePage');

class SettingsPage extends BasePage
{
    public function __construct()
    {
        $this->addRule(array(
            'delete' => '(delete): null',
            //'authorize' => '(authorize): null',
            'authorizeapp' => '(authorize)/([a-f0-9]{32}): null/token',
            'unsubscribe' => '(unsubscribe): null',
        ));
    }
    
    public function handleDefault()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('settings');
        
        $this->T->include('this.settings', 'content');
        $this->T->page_title = $this->LNG->Settings;
        $this->T->page_id = 'settings-page';
        return $this->T->return('templates.inner');
    }
    
    public function handleDelete()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('settings');
        
        $this->T->include('this.delete', 'content');
        $this->T->page_title = $this->LNG->Delete_account;
        $this->T->page_id = 'delete-page';
        return $this->T->return('templates.inner');
    }
    
    public function handleGetAuthorizeApp(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('settings');
        
        $Tokens = new PrimaryTable('request_tokens', 'id');
        $app_id = $Tokens->select('app', SQL::quote('id = ?', $this->ENV->token), null, 1);
        
        import('model.Apps');
        $Apps = new AppsModel;
        
        $this->T->app = $Apps->view($app_id);
        
        $this->T->include('this.authorize', 'content');
        $this->T->page_title = $this->LNG->Authorize_application;
        $this->T->page_id = 'authorize-page';
        if (detectmobilebrowser() && !$this->Session->desktop) {
            $this->T->no_desktop_link = true;
            return $this->T->return('templates.mobile');
        } else {
            return $this->T->return('templates.inner');
        }
    }
    
    public function handlePostAuthorizeApp(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('settings');
        
        $Tokens = new PrimaryTable('request_tokens', 'id');
        $app_id = $Tokens->select('app', SQL::quote('id = ?', $this->ENV->token), null, 1);
        
        import('model.Apps');
        $Apps = new AppsModel;
        
        $this->T->app = $app = $Apps->view($app_id);
        
        if (isset($request['deny'])) {
            $this->T->include('this.denied', 'content');
            $this->T->page_title = $this->LNG->Authorize_application;
            
        } elseif (isset($request['allow'])) {
            $Tokens->update($this->ENV->token, array('user' => $this->ENV->UID, 'status' => 'authorized'));
            if ($app['url']) {
                header('Location: ' . $app['url'] . '?oauth_token=' . $this->ENV->token);
                return true;
            }
            $this->T->token = $this->ENV->token;
            $this->T->include('this.allowed', 'content');
            $this->T->page_title = $this->LNG->Authorize_application . ' access_token=' . $this->ENV->token;
        }
        $this->T->page_id = 'authorize-page';
        if (detectmobilebrowser() && !$this->Session->desktop) {
            $this->T->no_desktop_link = true;
            return $this->T->return('templates.mobile');
        } else {
            return $this->T->return('templates.inner');
        }
    }
    
    public function handleUnsubscribe(array $request = array())
    {
        $email = array_get($request, 'email');
        $hash = array_get($request, 'h');
        $timestamp = array_get($request, 't');
        
        if (!$email || !$hash || !$timestamp) {
            return false;
        }
        
        $Users = new PrimaryTable('users', 'id');
        $user = $Users->select(array('id', 'password'), SQL::quote('email = ?', $email), null, 1);
        $token = md5("unsubscribe-$timestamp-{$user['password']}");
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('settings');
         
        if ($token == $hash) {
            $Users->update($user['id'], array('receive_emails' => false));
            $this->T->message = $this->LNG->Unsubscribe_success;
        } else {
            $this->T->message = $this->LNG->Unsubscribe_error;
        }
       
        $this->T->include('this.unsubscribe', 'content');
        $this->T->page_title = $this->LNG->Settings;
        return $this->T->return('templates.inner');
    }

    public function accountType($pay_til)
    {	
    }

}
?>
