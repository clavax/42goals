<?php
import('base.controller.BasePage');

class BlogAdminPage extends BasePage
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
        
        import('model.Posts');
        import('model.Categories');
        import('model.Templates');
        import('lib.json');
        
        $Posts = new PostsModel;
        $Categories = new CategoriesModel;
        $Templates = new TemplatesModel;
        $fields = array('id', 'title', 'date', 'template', 'name', 'text');
        $posts = $Posts->select($fields, SQL::quote('user = ?', $this->ENV->UID), array('date:desc', 'id'));
        
        $categories = $Categories->select(array('id', 'title' => 'title_' . $this->ENV->language), null, array('position', 'id'));
        
        $admin_id = $this->CNF->languages[$this->ENV->language]->admin;
        $admin_ids = array($this->ENV->UID);
        foreach ($this->CNF->languages as $name => $lang) {
            $admin_ids[] = $lang['admin'];
        }
        $templates = $Templates->select(array('id', 'title', 'category'), SQL::quote('user = ? and approved = ?', $admin_id, 'yes'), array('position', 'id'));
        
        $this->T->posts = $posts;
        $this->T->categories = $categories;
        $this->T->templates = $templates;
        
        $this->T->include('this.blog-admin', 'content');
        
        $this->T->page_title = 'Blog Admin';
        $this->T->page_id = 'posts-admin';
        return $this->T->return('templates.inner');
    }
}
?>