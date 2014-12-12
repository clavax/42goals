<?php
import('base.controller.BasePage');

class CorporatePage extends BasePage
{
    public function handle()
    {
        $this->Conf->loadLanguage('site');
        $this->T->include('this.corporate_' . $this->ENV->language, 'content');
        
        $this->T->page_title = $this->LNG->Corporate;
        $this->T->page_gray = true;
        $this->T->page_wide_footer = true;
        return $this->T->return('templates.inner');
    }
}
?>