<?php
import('base.controller.BaseUserPage');

class UserFeedPage extends BaseUserPage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'default' => '(?:p(\d+))?: page',
        ));
    }

    public function handleDefault()
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }
        
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        if ($user['id'] != $this->ENV->UID) {
            return false;
        }
        
        $this->T->user = $user;
                
        import('lib.locale');
        import('lib.page');
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('feed');
        
        $Feed = new DataTable('feed');
        $FeedView = new DataTable('feed_view');
        
        // get and set last view
        $last_view = $FeedView->select('time', SQL::quote('user = ?', $this->ENV->UID), null, 1);
        $now = date('Y-m-d H:i:s');
        
        if ($last_view) {
            $FeedView->update_where(array('time' => $now), SQL::quote('user = ?', $this->ENV->UID));
        } else {
            $FeedView->insert(array('time' => $now, 'user' => $this->ENV->UID));
        }

        $count = SQL::select('count(*)', 'feed');
               
        $select = SQL::select(array('id', 'user', 'time', 'type', 'data'), 'feed')
                ->join('communities', SQL::quote('communities.id = feed.community'), array('community_name' => 'name', 'community_title' => 'title'))
                ->join('users', SQL::quote('users.id = feed.user'), array('user_login' => 'login', 'user_name' => 'name', 'user_thumbnail' => 'thumbnail'))
                ->order('time', 'desc');
        
        // communities
        $Membership = new DataTable('community_membership');
        $communities_ids = $Membership->select('community', SQL::quote('community and user = ?', $this->ENV->UID));
        if ($communities_ids) {
            $condition = SQL::quote('feed.community in (?)', $communities_ids);
            $select->where($condition);
            $count->where($condition);
        }
        
        // friends
        $Connections = new DataTable('connections');
        $friends_ids = $Connections->select('user_to', SQL::quote('user_from = ? and status = ?', $this->ENV->UID, 'accepted'));
        if ($friends_ids) {
            $condition = SQL::quote('feed.user in (?)', $friends_ids);
            $select->or_where($condition);
            $count->or_where($condition);
        }
        
        if (!$friends_ids && !$communities_ids) {
            $this->T->curr_page = 0;
            $this->T->next_page = 0;
            $this->T->prev_page = 0;
            $this->T->activities = array();
        } else {   
            $count->and_where(SQL::quote('feed.user != ?', $this->ENV->UID));
            $select->and_where(SQL::quote('feed.user != ?', $this->ENV->UID));
            
            // paging
            $perpage = 30;
            $page = $this->ENV->page;
            $total = $this->db->value($count);
            list($start, $pages) = page::calculate($total, $page, $perpage);
            $this->T->curr_page = $page;
            $this->T->next_page = $page < $pages ? $page + 1 : 0;
            $this->T->prev_page = $page > 1 ? $page - 1 : 0;
            
            $select->limit($start, $perpage);
            
            $this->db->query($select);
            $this->T->activities = $this->db->fetch_all();
        }
        
        $this->T->include('this.feed', 'content');
        
        $this->T->page_title = $this->LNG->News_feed;
        $this->T->page_gray = true;
        
        return $this->T->return('templates.inner');
    }
}
?>