<?php
import('base.controller.BaseApi');
import('model.Users');

class InviteApi extends BaseApi
{
    public function handlePostDefault(array $request = array())
    {
        $email = $request['emails'];
        
        $Users = new UsersModel;
        $user_to = $Users->select('id', SQL::quote('email = ?', $email), null, 1);
        if (!$user_to) {
            // @todo: send an invitation to register and create a new user
            return $this->respondOk(array('error' => 'User does not exists'));
        }
        
        $UsersLink = new PrimaryTable('users_link', 'id');
        
        $time = time();
        $this->db->execute(SQL::quote('set @user_revision = ?', $time));
        if (!$UsersLink->exists_where(SQL::quote('user_from = ? and user_to = ?', $this->ENV->UID, $user_to))) {
            $UsersLink->insert(array('user_from' => $this->ENV->UID, 'user_to' => $user_to, 'status' => 1));
        }
        
        return $this->respondOk(array('ok' => 'ok'));
    }
}
?>