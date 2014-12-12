<?php
import('base.controller.BasePage');

class AppsPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'add' => '(add): null',
        ));
    }
    
    public function handleDefault()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('api');
        
        $this->T->include('this.list', 'content');
        $this->T->page_title = $this->LNG->Applications;
        $this->T->page_id = 'registration-page';
        return $this->T->return('templates.inner');
    }
    
    public function handleAdd()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('api');
        
        import('model.Apps');
        $Apps = new AppsModel;
        
        $this->T->app = $Apps->select('*', SQL::quote('user = ?', $this->ENV->UID), null, 1);
        
        $this->T->include('this.form', 'content');
        $this->T->page_title = $this->LNG->Add_application;
        $this->T->page_id = 'apps-page';
        return $this->T->return('templates.inner');
    }
}
?>