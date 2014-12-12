<?php
import('base.controller.BaseTag');

class UserProfileTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('me');
        $user = array_get($params, 'user');
        if (!$user) {
            return 'User is not defined';
        }
        
        $paid_till = strtotime($user['paid_till']);
        $user['premium'] = strlen($user['paid_till']) > 0;
        $user['valid'] = $user['premium'] && strtotime($user['paid_till']) > time();
        
        import('lib.url');
        $user['short_url'] = preg_replace('#^http://#', '', $user['url']);
        $user['short_url'] = preg_replace('#^www\.#', '', $user['short_url']);
        $user['short_url'] = url::shorten($user['short_url'], 24, '...');
        
        $this->T->user = $user;
        
        $communities = array();
        
        $Membership = new DataTable('community_membership');
        $communities_ids = $Membership->select('community', SQL::quote('user = ?', $user['id']));
        
        if ($communities_ids) {
            import('model.Communities');
            $Communities = new CommunitiesModel;
            $communities = $Communities->select(array('name', 'title', 'thumbnail'), SQL::quote('id in (?)', $communities_ids));
        }
        
        $this->T->communities = $communities;
        
        return $this->T->return('tag.user-profile');
    }
}