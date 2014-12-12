<?php
import('base.controller.BaseApi');
import('domains.api.v1.oauth.OAuthApi');

class GoalsApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'goal' => '(\d+): id'
        ));
    }
    
    public function handleGetDefault(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        
        $goals = $Goals->select(array('id', 'title', 'type', 'text', 'icon_item', 'icon_true', 'icon_false', 'position', 'unit', 'prepend', 'aggregate'), SQL::quote('user = ?', $this->ENV->UID), 'position');
        
        return $this->respondOk(array('goal' => $goals));
    }
    
    public function handlePostDefault(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }

        import('model.Goals');
        $Goals = new GoalsModel;
        
        $this->Conf->loadLanguage('goals');
                
        // get data
        $goal = $this->getGoalData($request);
        $goal['user'] = $this->ENV->UID;
        
        // try to add new goal
        if (($id = $Goals->add($goal)) === false) {
            $errors = $Goals->errors;
            foreach ($errors as $field => &$error) {
                $error = $this->LNG->get("Error_{$error}_{$field}", "Error_{$error}_{$field}");
            }
            return $this->respondOk(array('error' => $errors));
        }
        unset($goal['user']);
        $goal['id'] = $id;
        
        return $this->respondOk(array('goal' => $goal));
    }
        
    public function handleGetGoal(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        
        $goal = $Goals->view($this->ENV->id, array('id', 'user', 'title', 'type', 'text', 'icon_item', 'icon_true', 'icon_false', 'position', 'unit', 'prepend', 'aggregate'));
        if ($goal['user'] != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        unset($goal['user']);
        
        return $this->respondOk(array('goal' => $goal));
        
    }

    // edit a goal
    public function handlePutGoal(array $request = array())
    {
        if (!OAuthApi::isAuthorized($GLOBALS['_PUT'])) {
            return $this->respondOk(array('error' => 'not authorized'));
        }

        import('model.Goals');
        $Goals = new GoalsModel;
        
        $this->Conf->loadLanguage('goals');
        
        // check owner
        if ($Goals->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }

        // get data
        $goal = $this->getGoalData($request);
    
        // try to edit the goal
        if ($Goals->edit($this->ENV->id, $goal) === false) {
            $errors = $Goals->errors;
            foreach ($errors as $field => &$error) {
                $error = $this->LNG->get("Error_{$error}_{$field}", "Error_{$error}_{$field}");
            }
            return $this->respondOk(array('error' => $errors));
        }
        
        return $this->respondOk(array('goal' => $goal));        
    }
    
    // delete a goal
    public function handleDeleteGoal(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        
        // check owner
        if ($Goals->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        // try to edit goal
        if ($Goals->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => 'sql error'));
        }
        
        // delete goal data
        $Data = new DataTable('data');
        $Data->delete_where(SQL::quote('goal = ?', $this->ENV->id));
        
        // delete plans
        $Plan = new DataTable('plan');
        $Plan->delete_where(SQL::quote('goal = ?', $this->ENV->id));
        
        return $this->respondOk(array('ok' => 'ok'));
    }
    
    private function getGoalData($request)
    {
        $fields = array('title', 'text', 'type', 'icon_item', 'icon_zero', 'icon_true', 'icon_false', 'unit', 'prepend', 'aggregate');
        $data = array();
        foreach ($fields as $field) {
            if (isset($request[$field])) {
                $data[$field] = $request[$field];
            }
        }
        return $data;
    }
    
}
?>