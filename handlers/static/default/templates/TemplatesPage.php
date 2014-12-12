<?php
import('base.controller.BasePage');

class TemplatesPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'category' => '(\w+): id',
            'template' => '(\w+)/(\w+): category/template'
        ));
    }
    
    public function handleDefault()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        
        import('model.Categories');
        import('model.Templates');
        import('model.Icons');
        
        $Categories = new CategoriesModel;
        $Templates = new TemplatesModel;
        $Icons = new IconsModel;
        
        $categories = $Categories->select(array('id', 'title' => 'title_' . $this->ENV->language, 'name'), null, array('position', 'id'));
        
        $admin_id = $this->CNF->languages[$this->ENV->language]->admin;
        $admin_ids = array($this->ENV->UID);
        foreach ($this->CNF->languages as $name => $lang) {
            $admin_ids[] = $lang['admin'];
        }
        $templates = $Templates->select(array('id', 'title', 'name', 'preview', 'type', 'icon_item', 'icon_zero', 'icon_true', 'icon_false', 'unit', 'prepend', 'aggregate', 'category'), SQL::quote('user = ? and approved = ?', $admin_id, 'yes'), array('position', 'id'));
        
        $icons = arrays::map($Icons->select(array('id', 'src'), SQL::quote('user = ? or user in (?)', $this->ENV->UID, $admin_ids), array('position', 'id')), 'id', 'src');
        foreach ($templates as &$template) {
            $template['icon'] = $this->URL->img . 'icons/loading.png';
            switch ($template['type']) {
            case 'counter':
                if (isset($icons[$template['icon_item']])) {
                    $template['icon'] = $icons[$template['icon_item']];
                }
                break;
            case 'boolean':
                if (isset($icons[$template['icon_true']])) {
                    $template['icon'] = $icons[$template['icon_true']];
                }
                break;
            case 'numeric':
                $template['icon'] = $this->URL->img . 'icons/number.png';
                break;
            case 'time':
                $template['icon'] = $this->URL->img . 'icons/time.png';
                break;
            case 'timer':
                $template['icon'] = $this->URL->img . 'icons/stopwatch.png';
                break;
            }
             
        }
        
        $this->T->icons = $icons;
        $this->T->categories = $categories;
        $this->T->categories3 = arrays::divide($categories, 3);
        $this->T->templates = $templates;
        
        $this->T->include('this.templates', 'content');
        
        $this->T->page_title = $this->LNG->Templates;
        $this->T->page_id = 'templates-page';
        $this->T->page_gray = true;
        return $this->T->return('templates.inner');
    }
    
    public function handleTemplate(array $request = array())
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        
        import('model.Categories');
        import('model.Templates');
        import('model.Icons');
        import('model.Posts');
        
        $Categories = new CategoriesModel;
        $Templates = new TemplatesModel;
        $Icons = new IconsModel;
        $Posts = new PostsModel;
        
        // get category
        $category = $Categories->select(array('id', 'name', 'title' => 'title_' . $this->ENV->language), SQL::quote('name = ?', $this->ENV->category), 'id', 1);
        if (!$category) {
            return false;
        }
        $this->T->category = $category;
        
        // get template
        
        $fields = array('id', 'type', 'icon_item', 'icon_true', 'title', 'preview', 'description', 'instructions');
        $template = $Templates->select($fields, SQL::quote('name = ? and category = ?', $this->ENV->template, $category['id']), 'id', 1);
        if (!$template) {
            return false;
        }
        
        switch ($template['type']) {
        case 'counter':
            $template['icon'] = $Icons->view($template['icon_item'], 'src');
            break;
        case 'boolean':
            $template['icon'] = $Icons->view($template['icon_true'], 'src');
            break;
        case 'numeric':
            $template['icon'] = $this->URL->img . 'icons/number.png';
            break;
        case 'time':
            $template['icon'] = $this->URL->img . 'icons/time.png';
            break;
        case 'timer':
            $template['icon'] = $this->URL->img . 'icons/stopwatch.png';
            break;
        }
        $this->T->template = $template;
        
        // get posts
        $posts = $Posts->select('*', SQL::quote('template = ?', $this->T->template['id']), 'date:desc');
        $this->T->posts = $posts;
        
        $this->T->include('this.template', 'content');
        $this->T->page_title = $this->T->template['title'];
        $this->T->page_id = 'templates-page';
        $this->T->page_gray = true;
        return $this->T->return('templates.inner');
    }
}
?>