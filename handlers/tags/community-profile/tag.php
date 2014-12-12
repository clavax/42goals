<?php
import('base.controller.BaseTag');

class CommunityProfileTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('communities');
        $this->T->community = $params['community'];
        return $this->T->return('tag.community-profile');
    }
}