<?php
import('base.controller.BasePage');

class Error403Page extends BasePage
{
    public function handle()
    {
        $this->Conf->loadLanguage('site');
        $this->T->include('handlers.error.403.content');        
        $this->T->page_title = $this->LNG->Title_403;
        header('HTTP/1.1 403 Forbidden');
        return $this->T->return('templates.main');        
    }
}
?>
