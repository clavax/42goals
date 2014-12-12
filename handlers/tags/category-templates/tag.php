<?php
import('base.controller.BaseTag');

class CategoryTemplatesTag extends BaseTag
{
    public function handle(array $params = array())
    {
        import('model.Templates');
        import('model.Categories');
        import('model.Icons');
        
        $Templates = new TemplatesModel;
        $Categories = new CategoriesModel;
        $Icons = new IconsModel;
        
        // get other templates in this category
        $admin_id = $this->CNF->languages[$this->ENV->language]->admin;
        $admin_ids = array($this->ENV->UID);
        foreach ($this->CNF->languages as $name => $lang) {
            $admin_ids[] = $lang['admin'];
        }
        
        $category_id = $params['category']['id'];
        $templates = $Templates->select(array('id', 'title', 'category',  'name', 'type', 'icon_item', 'icon_true'), SQL::quote('user = ? and approved = ?', $admin_id, 'yes'), array('position', 'id'));
        
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
        $this->T->templates = $templates;
        
        $this->T->categories = $Categories->select(array('id', 'title' => 'title_' . $this->ENV->language, 'name'), null, array('position', 'id'));
        
        $this->T->current = $params['template'];
        $this->T->current_category = $params['category'];        
        return $this->T->return('tag.category-templates');
    }
}