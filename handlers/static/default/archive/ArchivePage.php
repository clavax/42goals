<?php
import('base.controller.BasePage');
import('lib.locale');

class ArchivePage extends BasePage
{
    public function handle()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        
        import('model.Goals');
        import('model.Icons');
        import('lib.json');
        
        $Goals = new GoalsModel;
        $fields = array(
            'id', 
            'title', 
            'archived',
            'type', 
            'icon_item', 
            'icon_zero', 
            'icon_true', 
            'icon_false', 
        );
        $predicate = SQL::quote('user = ? and archived is not NULL', $this->ENV->UID);
        $goals = $Goals->select($fields, $predicate, array('archived', 'id'));
        
        $Icons = new IconsModel;
        $icons = arrays::map($Icons->select(array('id', 'src'), SQL::quote('user = ? or user = 1', $this->ENV->UID), array('position', 'id')), 'id', 'src');
        
        $this->T->today = date('Y-m-d');
        $this->T->goals = array_values($goals);
        $this->T->icons = $icons;
        $this->T->include('this.archive-list', 'content');
        
        $this->T->page_title = $this->LNG->Archive;
        $this->T->page_id = 'archive-page';
        return $this->T->return('templates.inner');
    }
}
?>