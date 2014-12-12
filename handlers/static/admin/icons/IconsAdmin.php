<?php
import('base.controller.BasePage');

class IconsAdmin extends BasePage
{
    public function handleDefault()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        if (!Access::isAdmin()) {
            return false;
        }
        
        $this->Conf->loadLanguage('site');
        
        import('model.Icons');
        import('lib.json');
        
        $Icons = new IconsModel;
        $fields = array('id', 'user', 'src');
        $icons = $Icons->select($fields, SQL::quote('user = ?', $this->ENV->UID), array('position', 'id'));
        
        $this->T->icons = array_values($icons);
        
        $this->T->include('this.page', 'content');
        
        $this->T->page_id = 'page-icons';
        return $this->T->return('templates.admin');
    }
}
?>