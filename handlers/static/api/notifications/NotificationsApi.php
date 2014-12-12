<?php
import('base.controller.BaseApi');

class NotificationsApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'item' => '(\d+): id',
            'readall' => '(readall): null',
        ));
    }
    
    public function handlePostItem()
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Notifications = new PrimaryTable('notifications', 'id');
        
        if ($Notifications->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not owner'));
        }
        
        $Notifications->update($this->ENV->id, array('is_read' => 'yes'));
        
        return $this->respondOk(array('ok' => 1));
    }
    
    public function handlePostReadAll()
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Notifications = new PrimaryTable('notifications', 'id');
        $Notifications->update_where(array('is_read' => 'yes'), SQL::quote('user = ?', $this->ENV->UID));
        
        return $this->respondOk(array('ok' => 1));
    }
}
?>