<?php
import('base.controller.BaseTag');

class FeedCountTag extends BaseTag
{
    public function handle(array $params = array())
    {
        if (Access::loggedIn()) {
            $this->Conf->loadLanguage('me');
            $this->Conf->loadLanguage('feed');
            
            $FeedView = new DataTable('feed_view');
            $last_view = $FeedView->select('time', SQL::quote('user = ?', $this->ENV->UID), null, 1);
            
            $select = SQL::select('count(*)', 'feed');
            
            // communities
            $Membership = new DataTable('community_membership');
            $communities_ids = $Membership->select('community', SQL::quote('community and user = ?', $this->ENV->UID));
            if ($communities_ids) {
                $select->or_where(SQL::quote('feed.community in (?)', $communities_ids));
            }
            
            // friends
            $Connections = new DataTable('connections');
            $friends_ids = $Connections->select('user_to', SQL::quote('user_from = ? and status = ?', $this->ENV->UID, 'accepted'));
            if ($friends_ids) {
                $select->or_where(SQL::quote('feed.user in (?)', $friends_ids));
            }
            
            $select->and_where(SQL::quote('time > ? and feed.user != ?', $last_view, $this->ENV->UID));
            
            $this->T->new_feeds = $this->db->value($select);
            
            return $this->T->return('tag.feed');
        } else {
            return false;
        }
    }
}