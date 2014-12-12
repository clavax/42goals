<?php
import('base.controller.BasePage');

class TemplatesAdminPage extends BasePage
{
    public function handle()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        if (!Access::isAdmin()) {
            return false;
        }
        
        $this->Conf->loadLanguage('site');
        
        import('model.Communities');
        import('lib.json');
        
        $Communities = new CommunitiesModel;
        $fields = array('id', 'user', 'title', 'name', 'picture', 'overview', 'description');
        $communities = $Communities->select($fields, SQL::quote('user = ?', $this->ENV->UID), 'id');
        
        $this->T->communities = array_values($communities);
        
        $this->T->include('this.communities-admin', 'content');
        
        $this->T->page_id = 'communities-admin';
        return $this->T->return('templates.admin');
    }
}
?>