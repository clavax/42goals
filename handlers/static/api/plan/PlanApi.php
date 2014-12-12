<?php
import('base.controller.BaseApi');

class PlanApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(
            array(
                'plan' => '(\d+): id',
            )
        );
    }
    
    // add a new plan
    public function handlePostDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Goals');
        import('model.Plan');
        $Goals = new GoalsModel;
        $Plan  = new PlanModel;
        
        $data = $this->getData($request);
        
        // check owner
        if ($Goals->view($data['goal'], 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }

        if (($id = $Plan->add($data)) === false) {
            return $this->respondOk(array('error' => $this->translateErrors($Plan->errors)));
        }
        
        return $this->respondOk(array('item' => $this->viewData($Plan, $id)));
        
    }
    
    // edit plan
    public function handlePutPlan(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Goals');
        import('model.Plan');
        $Goals = new GoalsModel;
        $Plan  = new PlanModel;
        
        $data = $this->getData($request);
        
        // check owner
        $goal = $Plan->view($this->ENV->id, 'goal');
        if ($Goals->view($goal, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        if (isset($data['goal']) && $Goals->view($data['goal'], 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }

        if ($Plan->edit($this->ENV->id, $data) === false) {
            return $this->respondOk(array('error' => $this->translateErrors($Plan->errors)));
        }
        
        return $this->respondOk(array('item' => $this->viewData($Plan, $this->ENV->id)));
        
    }
    
    // delete plan
    public function handleDeletePlan(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Goals');
        import('model.Plan');
        $Goals = new GoalsModel;
        $Plan  = new PlanModel;
        
        // check owner
        $goal = $Plan->view($this->ENV->id, 'goal');
        if ($Goals->view($goal, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }

        if ($Plan->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => $this->translateErrors($Plan->errors)));
        }
        
        return $this->respondOk(array('ok' => 'ok'));
        
    }
    
    private function getData($request) {
        $fields = array('goal', 'startdate', 'enddate', 'value', 'text');
        $data = array();
        foreach ($fields as $field) {
            if (isset($request[$field])) {
                $data[$field] = $request[$field];
            }
        }
        return $data;
    }
    
    private function viewData($obj, $id) {
        return $obj->view($id, array('id', 'goal', 'startdate', 'enddate', 'value', 'text'));
    }
    
    private function translateErrors($errors) {
        $translated = $errors;
        foreach ($errors as $field => $error) {
            $translated[$error] = $this->LNG->get("Error_{$error}_{$field}", "Error_{$error}_{$field}");
        }
        
        return $translated;
    }
}