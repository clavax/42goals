<?php
import('base.controller.BasePage');

class PremiumPage extends BasePage
{
    public function __construct()
    {
        $this->addRule(array(
            'buy' => '(buy): null',
            'success' => '(success): null',
            'pending' => '(pending): null',
        ));
    }
    
    public function handle(array $request = array()) 
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('premium');
        
        $this->T->include('this.premium_' . $this->ENV->language, 'content');
        
        $this->T->page_title = $this->LNG->Premium;
        $this->T->page_id = 'premium-page';
        $this->T->page_gray = true;
        $this->T->page_wide_footer = true;
        return $this->T->return('templates.inner');
    }
}
?>