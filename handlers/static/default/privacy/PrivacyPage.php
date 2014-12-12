<?php
import('base.controller.BasePage');

class PrivacyPage extends BasePage
{
    public function handle()
    {
        $this->Conf->loadLanguage('site');
        $this->T->include('this.policy', 'content');
        
        $this->T->page_title = $this->LNG->Privacy_policy;
        return $this->T->return('templates.inner');
    }
}
?>