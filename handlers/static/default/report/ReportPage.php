<?php
import('base.controller.BasePage');

class ReportPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'goal' => '(\d+): id'
        ));
    }
    
    public function handleDefault()
    {
        return 'oh hai';
    }
    
    public function handleGoal()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        import('model.Goals');
        import('lib.json');
        
        $this->Conf->loadLanguage('site');
        
        $Goals = new GoalsModel;
        
        if ($Goals->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return false;
        }
        
        $Data = new DataTable('data');
        $data = arrays::by_field($Data->select(array('date', 'value'), SQL::quote('goal = ?', $this->ENV->id), 'date'), 'date');
        
        $this->T->data = $data;

        $this->T->today = date('Y-m-d');
        $this->T->goal = $goal = $Goals->view($this->ENV->id, array('title', 'text', 'type'));
        $this->T->data = $data;
        
        $this->T->include('this.page', 'content');
        
        $this->T->page_title = $goal['title'];
        $this->T->page_id = 'report-page';
        return $this->T->return('templates.inner');        
    }
}
?>