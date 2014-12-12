<?php
import('base.controller.BasePage');

class TractionPage extends BasePage
{
    public function handle()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        $this->Conf->loadLanguage('site');
        
        $Data = new DataTable('data');
        $Users = new DataTable('users');
        $start = '2011-03-06';
        $data_by_date = $Data->select(array('created', 'count' => 'count(distinct user)'), SQL::quote('created > ?', $start), 'created', null, 'created');
        $users_by_date = $Users->select(array('registered', 'count' => 'count(*)'), SQL::quote('registered > ?', $start), 'registered', null, 'registered');

        $data_by_user = $Data->select(array('user', 'count' => 'count(distinct created)'), SQL::quote('created > ?', $start), 'count', null, 'user');
        $data_by_count = array();
        foreach ($data_by_user as $data) {
            if (!isset($data_by_count[$data['count']])) {
                $data_by_count[$data['count']] = 0;
            }
            $data_by_count[$data['count']] ++;
        }
        
        $this->T->data_by_date  = $data_by_date;
        $this->T->data_by_count = $data_by_count;
        $this->T->users_by_date = $users_by_date;
        
        $this->T->include('this.traction', 'content');
        
        $this->T->page_title = 'Traction';
        $this->T->page_gray = true;
        return $this->T->return('templates.inner');
    }
}