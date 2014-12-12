<?php
import('base.controller.BaseTag');

class MainMenuTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('site');
        $this->T->menu = array(
            array(
                'title' => $this->LNG->About_us,
                'url'   => $this->URL->home . 'about/'
            ),
            array(
                'title' => $this->LNG->Demo,
                'url'   => $this->URL->home . 'demo/'
            ),

	    array(
                'title' => $this->LNG->User_Guide,
                'url'   => $this->URL->home . 'userguide/'
            ),
            array(
                'title' => $this->LNG->Plans_and_Pricing,
                'url'   => $this->URL->home . 'premium/'
            ),
            array(
                'title' => $this->LNG->Users,
                'url'   => $this->URL->home . 'users/'
            ),
            array(
                'title' => $this->LNG->Communities,
                'url'   => $this->URL->home . 'communities/'
            ),
            array(
                'title' => $this->LNG->Shop,
                'url'   => $this->URL->home . 'shop/'
            ),
        );
        
        $this->T->query = preg_replace('/^' . preg_quote($this->URL->home, '/') . '/', '', $this->URL->self);
        $this->T->languages = $this->CNF->languages;
        
        return $this->T->return('tag.main-menu');
    }
}
