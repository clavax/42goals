<?php
import('base.controller.BaseController');
import('3dparty.detectmobilebrowser');

abstract class BasePage extends BaseController 
{  
    public function __construct()
    {
    }
    
    public function showLogin()
    {
        import('static.default.login.LoginPage');
        $this->PTH->this = path('static.default.login') . '/';
        $LoginPage = new LoginPage;
        if (detectmobilebrowser() && !$this->Session->desktop) {
            return $LoginPage->handleMobile(array('redirect' => $this->URL->self));
        } else {
            return $LoginPage->handleDefault(array('redirect' => $this->URL->self));
        }
    }
}
?>
