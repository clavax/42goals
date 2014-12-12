<?php
import('base.controller.BasePage');

class BaseMobilePage extends BasePage
{
    public function handlePostDefault($request)
    {
        import('handlers.static.api.login.LoginApi');
        $LoginApi = new LoginApi;
        $result = $LoginApi->handlePostDefault($request);
        $xml = new DOMDocument();
        $xml->loadXML($result);
        if ($xml->getElementsByTagName('ok')->length) {
            $sid = $xml->getElementsByTagName('sid')->item(0)->nodeValue;
            if ($xml->getElementsByTagName('remember')->item(0)->nodeValue) {
                $location = $this->CNF->site->sso . 'setcookie/' . $sid . '/?redirect=' . urlencode($this->URL->self);
            } else {
                $location = $this->CNF->site->sso . 'setsession/' . $sid . '/?redirect=' . urlencode($this->URL->self);
            }
            header('Location: ' . $location);
            return true;
        } else {
            return $this->showLogin($request, true);
        }
    }
    
    public function showLogin($request = array(), $error = false)
    {
        $this->Conf->loadLanguage('login');
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        
        $this->T->data = $request;
        $this->T->error = $error;
        
        $this->T->include('templates.mobile-login', 'content');
        $this->T->page_title = $this->LNG->Title_403;
        
        return $this->T->return('templates.mobile');
    }
    
    public function showNotFound($request = array())
    {
        $this->Conf->loadLanguage('site');
    	
        $this->T->content = $this->LNG->Error_404;
        $this->T->page_title = $this->LNG->Title_404;
    	
    	return $this->T->return('templates.mobile');
    }
}