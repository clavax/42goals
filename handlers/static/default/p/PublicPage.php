<?php
import('base.controller.BasePage');

class PublicPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        
        $this->addRule(array(
            'url' => '(\w+): url',
            'img' => '(\w+\.png): url'
        ));
    }
    
    public function handleGetUrl(array $request = array())
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('users');
        $this->Conf->loadLanguage('registration');
        
        import('model.Goals');
        $Goals = new GoalsModel;
        $Users = new UsersModel;
        
        $Shared = new DataTable('shared');
        $data = $Shared->select('*', SQL::quote('id = ?', $this->ENV->url), null, 1);
        $this->T->data = $data['data'];
        
        $goal = $Goals->view($data['goal'], 'title');
        $user = $Users->view($data['user'], 'name');
        
        $this->T->user = $user;
        $this->T->goal = $goal;
        
        $this->T->include('this.content');
        $this->T->page_title = "$user, $goal chart";
        $this->T->page_id = 'public-page';
        
        return $this->T->return('templates.inner');
    }
    
    public function handleGetImg(array $request = array())
    {
        $Shared = new DataTable('shared');
        $id = substr($this->ENV->url, 0, -4);
        $data = $Shared->select('data', SQL::quote('id = ?', $id), null, 1);
        
        header('Location: http://chart.apis.google.com/chart?' . $data);
        
        return true;
    }
}
?>