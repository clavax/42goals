<?php
import('base.controller.BaseTag');

class UserboxTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('premium');
        $this->Conf->loadLanguage('me');
        
        if (Access::loggedIn()) {
            $this->T->paid_till = $this->ENV->user->paid_till;
        }
        return $this->T->return('tag.userbox');
    }
}