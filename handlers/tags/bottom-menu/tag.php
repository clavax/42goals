<?php
import('base.controller.BaseTag');

class BottomMenuTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('site');
        
        $this->T->menu = array(
//            array(
//                'title' => $this->LNG->About_us,
//                'url'   => '//' . $this->ENV->host . $this->URL->home . 'about/'
//            ),
            array(
                'title' => $this->LNG->Blog,
                'url'   => 'http://blog.42goals.com'
            ),
            array(
                'title' => $this->LNG->Mobile_version,
                'url'   => '//' . $this->ENV->host . $this->URL->home . 'm/'
            ),
            array(
                'title' => $this->LNG->API,
                'url'   => 'http://api.42goals.com'
            ),
            array(
                'title' => $this->LNG->Corporate,
                'url'   => '//' . $this->ENV->host . $this->URL->home . 'corporate/'
            ),
            array(
                'title' => $this->LNG->Privacy_policy,
                'url'   => '//' . $this->ENV->host . $this->URL->home . 'privacy/'
            ),
        );
        return $this->T->return('tag.bottom-menu');
    }
}