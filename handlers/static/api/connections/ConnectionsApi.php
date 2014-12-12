<?php
import('base.controller.BaseApi');
import('model.Communities');

class ConnectionsApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'item' => '(\d+): id',
        ));
    }
    
    public function handlePostItem()
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Connections = new DataTable('connections');
        /*
            A -> B
            
            A .. B => 'requested'
            A <- B => 'accepted'
            
         */
        
        if (!$Connections->exists_where(SQL::quote('user_from = ? and user_to = ?', $this->ENV->UID, $this->ENV->id))) {
            $status = $Connections->select('status', SQL::quote('user_from = ? and user_to = ?', $this->ENV->id, $this->ENV->UID), null, 1);
            if (!$status) {
                $Connections->insert(array('user_from' => $this->ENV->UID, 'user_to' => $this->ENV->id, 'status' => 'requested'));
                $template = 'connection-request';
            } else if ($status == 'requested') {
                $Connections->insert(array('user_from' => $this->ENV->UID, 'user_to' => $this->ENV->id, 'status' => 'accepted'));
                $Connections->update_where(array('status' => 'accepted'), SQL::quote('user_from = ? and user_to = ?', $this->ENV->id, $this->ENV->UID));
                $template = 'connection-accepted';
                
                $Users = new PrimaryTable('users', 'id');
                $user_from = $Users->view($this->ENV->UID, array('name', 'login', 'thumbnail'));
                $user_to   = $Users->view($this->ENV->id, array('name', 'login', 'thumbnail'));

                $Feed = new DataTable('feed');
                if (!$Feed->count('*', SQL::quote('resource = ?', "friend-{$this->ENV->UID}-{$this->ENV->id}"))) {
                    $Feed->insert(array(
                        'resource' => "friend-{$this->ENV->UID}-{$this->ENV->id}",
                        'user' => $this->ENV->UID,
                        'community' => 0,
                        'type' => 'friend-added',
                        'data' => json::encode($user_to),
                        'time' => date('Y-m-d H:i:s')
                    ));
                }
                if (!$Feed->count('*', SQL::quote('resource = ?', "friend-{$this->ENV->id}-{$this->ENV->UID}"))) {
                    $Feed->insert(array(
                        'resource' => "friend-{$this->ENV->id}-{$this->ENV->UID}",
                        'user' => $this->ENV->id,
                        'community' => 0,
                        'type' => 'friend-added',
                        'data' => json::encode($user_from),
                        'time' => date('Y-m-d H:i:s')
                    ));
                }
            }
            
            // send notification
            import('lib.notification.notification');
            notification::send($this->ENV->id, $template, null, '{@host}{@home}users/{$user["login"]}/friends/');
        }
        
        return $this->respondOk(array('status' => 1));
    }
    
    public function handleDeleteItem()
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Connections = new DataTable('connections');
        $status = $Connections->select('status', SQL::quote('user_from = ? and user_to = ?', $this->ENV->id, $this->ENV->UID), null, 1);
        if ($status == 'accepted') {
            $Connections->update_where(array('status' => 'requested'), SQL::quote('user_from = ? and user_to = ?', $this->ENV->id, $this->ENV->UID));
        }
        $Connections->delete_where(SQL::quote('user_from = ? and user_to = ?', $this->ENV->UID, $this->ENV->id));
        
        return $this->respondOk(array('status' => 0));
    }
}
?>