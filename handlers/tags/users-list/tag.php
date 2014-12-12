<?php
import('base.controller.BaseTag');

class UsersListTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('me');
        $users = array_get($params, 'users');
        if (!$users) {
            return 'No users';
        }
        
        $this->T->show_community_tools = array_get($params, 'community_tools', 'no');
        $this->T->users = $users;
        return $this->T->return('tag.users-list');
    }
}