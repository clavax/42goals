<?php
import('base.controller.BasePage');

class RegistrationPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'recovery' => '(recovery): null',
            'confirmation' => '(confirmation): null',
        ));
    }
    
    public function handleDefault()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('registration');
        
        $this->T->include('this.registration', 'content');
        $this->T->page_title = $this->LNG->Registration;
        $this->T->page_id = 'registration-page';
        return $this->T->return('templates.inner');
    }
    
    public function handleRecovery()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('registration');
        
        $this->T->include('this.recovery', 'content');
        $this->T->page_title = $this->LNG->Password_recovery;
        $this->T->page_id = 'recovery-page';
        return $this->T->return('templates.inner');
    }
    
    public function handleConfirmation()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('registration');
        
        $this->T->include('this.confirmation', 'content');
        $this->T->page_title = $this->LNG->Email_confirmation;
        $this->T->page_id = 'confirmation-page';
        return $this->T->return('templates.inner');
    }
}
?>