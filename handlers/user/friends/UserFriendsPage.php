<?php
import('base.controller.BaseUserPage');

class UserFriendsPage extends BaseUserPage
{
    public function handleDefault()
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('me');
        
        $Connections = new DataTable('connections');
        $Users = new DataTable('users');
        
        import('static.api.users.UsersApi');
        
        // get requests
        if (Access::loggedIn() && $user['id'] == $this->ENV->UID) {
            $requests_ids = $Connections->select('user_from', SQL::quote('user_to = ? and status = ?', $user['id'], 'requested'));
            if ($requests_ids) {
                $user['requests_in'] = UsersApi::selectUsers($requests_ids);
            }
            
            $requests_ids = $Connections->select('user_to', SQL::quote('user_from = ? and status = ?', $user['id'], 'requested'));
            if ($requests_ids) {
                $user['requests_out'] = UsersApi::selectUsers($requests_ids);
            }
        }
        
        // get friends
        $friends_ids = $Connections->select('user_to', SQL::quote('user_from = ? and status = ?', $user['id'], 'accepted'));
        if ($friends_ids) {
            $user['friends'] = UsersApi::selectUsers($friends_ids);
        }
        
        $this->T->user = $user;
        $this->T->include('this.users-friends-page', 'content');
        
        $this->T->page_title = $this->LNG->Friends . ' / ' . htmlspecialchars(strip_tags($user['name']));
        $this->T->page_gray = true;
        $this->T->page_id = 'users-posts-page';
        return $this->T->return('templates.inner');
    }
}
?>