<?php
import('base.controller.BaseTag');

class NotificationsTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('me');
        
        if (Access::loggedIn()) {
            $Notifications = new DataTable('notifications');
            import('lib.locale');
            $this->T->notifications = $Notifications->select(array('id', 'text', 'url', 'time'), SQL::quote('user = ? and is_read = ?', $this->ENV->UID, 'no'), 'time:desc');
            return $this->T->return('tag.notifications');
        } else {
            return false;
        }
    }
}