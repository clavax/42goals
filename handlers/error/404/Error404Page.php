<?php
import('base.controller.BasePage');

class Error404Page extends BasePage
{
    public function handle()
    {
        $this->Conf->loadLanguage('site');
        $this->T->include('handlers.error.404.content');        
        $this->T->page_title = $this->LNG->Title_404;
        headers::not_found();
        return $this->T->return('templates.inner');        
    }
}
?>