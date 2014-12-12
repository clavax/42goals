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
        $this->Conf->loadLanguage('goals');
        
        import('model.Templates');
        import('model.Icons');
        import('model.Categories');
        import('lib.json');
        
        $Templates = new TemplatesModel;
        $fields = array('id', 'user', 'title', 'name', 'preview', 'description', 'instructions', 'type', 'icon_item', 'icon_true', 'icon_false', 'unit', 'prepend', 'aggregate', 'approved', 'category');
        $templates = $Templates->select($fields, SQL::quote('user = ?', $this->ENV->UID), array('position', 'id'));
        
        $Categories = new CategoriesModel;
        $fields = array('id', 'title_en', 'title_ru', 'title_fr', 'name', 'position');
        $categories = $Categories->select($fields, null, array('position', 'id'));
        
        $Icons = new IconsModel;
        $admin_ids = array($this->ENV->UID); // current user
        foreach ($this->CNF->languages as $name => $lang) {
            $admin_ids[] = $lang['admin'];
        }
        $icons = arrays::map($Icons->select(array('id', 'src'), SQL::quote('user in (?)', $admin_ids), array('position', 'id')), 'id', 'src');
        
        $this->T->templates = array_values($templates);
        $this->T->categories = array_values($categories);
        $this->T->icons = $icons;
        
        $this->T->include('this.page', 'content');
        
        $this->T->page_id = 'templates-admin';
        return $this->T->return('templates.admin');
    }
}
?>