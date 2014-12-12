<?php
import('base.controller.BasePage');

class HomePage extends BasePage
{
    public function handleDefault(array $request = array())
    {
        if (isset($request['desktop'])) {
            $this->Session->desktop = true;
        }
        if (detectmobilebrowser() && !$this->Session->desktop) {
            // mobile browser
            if (Access::loggedIn()) {
                header('Location: ' . $this->URL->home . 'm/');
                return true;
            } else {
                $this->Conf->loadLanguage('site');
                $this->Conf->loadLanguage('users');
                $this->Conf->loadLanguage('login');
                
                $this->T->include('this.mobile_' . $this->ENV->language, 'content');
                $this->T->page_id = 'mobile-home-page';
                
                return $this->T->return('templates.mobile');
            }
        }
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('registration');
        
        $this->T->include('this.home_' . $this->ENV->language, 'content');
        $this->T->page_title = $this->LNG->home_title;
        $this->T->page_id = 'home-page';
        $this->T->page_gray = true;
        $this->T->page_wide_footer = true;
        return $this->T->return('templates.inner');
    }
}
?>