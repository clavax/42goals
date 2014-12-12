<?php
import('base.controller.BasePage');

class DonatePage extends BasePage
{
    public function handle()
    {
        $this->Conf->loadLanguage('site');
        $this->T->include('this.donate_' . $this->ENV->language, 'content');
        
        $this->T->page_title = $this->LNG->Donate;
        $this->T->page_id = 'donate-page';
        $this->T->page_gray = true;
        $this->T->page_wide_footer = true;
        return $this->T->return('templates.inner');
    }
}
?>