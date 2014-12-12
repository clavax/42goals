<?php
import('base.controller.BasePage');

class BadgesPage extends BasePage
{
    public function handleDefault()
    {
        $this->Conf->loadLanguage('site');
        $badges = array();
        if (Access::loggedIn()) {
            $Badges = new DataTable('badges');
            $badges = $Badges->select('type', SQL::quote('user = ?', $this->ENV->UID));
        }
        $this->T->badges = $badges;
        $this->T->include('this.badges', 'content');
        $this->T->page_gray = true;
        $this->T->page_title = $this->LNG->Badges;
        return $this->T->return('templates.inner');
    }
}
?>