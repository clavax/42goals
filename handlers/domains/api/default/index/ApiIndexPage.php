<?php
import('base.controller.BasePage');

class ApiIndexPage extends BasePage
{
    public function handleDefault($query)
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('api');
        $this->T->include('this.api-index', 'content');
        
        $this->T->page_title = $this->LNG->Api;
        return $this->T->return('templates.api');
    }
}
?>