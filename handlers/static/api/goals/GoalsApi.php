<?php
import('base.controller.BaseApi');

class GoalsApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(
            array(
                'goal' => '(\d+): id',
                'data' => '(\d+)/(\d{4}-\d{2}-\d{2})/(data): id/date/null',
                'plan' => '(\d+)/(\d{4}-\d{2}-\d{2})/(plan): id/date/null',
            )
        );
	
    }
   
    // add a new goal
    public function handlePostDefault(array $request = array())
    {
  
	
	   if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Goals');
        $Goals = new GoalsModel;
        
        $this->Conf->loadLanguage('goals');
                
        // get data
		$data = $this->getGoalData($request);
		
		
        $data['user'] = $this->ENV->UID;
        
        // get icons
        $new_icons = self::getIcons($data);
        
		//print_r($data);exit;
        // try to add new goal
        if (($id = $Goals->add($data)) === false) {
            $errors = $Goals->errors;
            
            foreach ($errors as $field => &$error) {
                $error = $this->LNG->get("Error_{$error}_{$field}", "Error_{$error}_{$field}");
            }
            
            return $this->respondOk(array('error' => $errors));
        }
        
		//$kk = $this->viewGoal($id);
		//print_r($kk);
			
        // add icons
		//var_dump($new_icons);exit;
		
        $data = self::addIcons($new_icons);
        
        // update goal
		
		
        $Goals->edit($id, $data);
        
        $item = $this->viewGoal($id);
		
		
        return $this->respondOk(array('item' => $item, 'icon' => array_values($new_icons)));
    }
       
    // edit a goal and save its icons
    public function handlePutGoal(array $request = array())
    {
       
		
		if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        import('model.Goals');
        import('model.Icons');
        $Goals = new GoalsModel;
        $Icons = new IconsModel;
        
        $this->Conf->loadLanguage('goals');
        
        // check owner
        if ($Goals->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }

        // get data
        $data = $this->getGoalData($request);
    
        
		// get icons
        $new_icons = self::getIcons($data);
		
		
        // try to edit the goal
        if ($Goals->edit($this->ENV->id, $data) === false) {
            $errors = $Goals->errors;
            
            foreach ($errors as $field => &$error) {
                $error = $this->LNG->get("Error_{$error}_{$field}", "Error_{$error}_{$field}");
            }
            
            return $this->respondOk(array('error' => $errors));
        }
        
        // add icons
        $data = self::addIcons($new_icons);
        
		
        // update goal
        $Goals->edit($this->ENV->id, $data);
        
        $item = $this->viewGoal($this->ENV->id);
		
        return $this->respondOk(array('item' => $item, 'icon' => array_values($new_icons)));        
    }
 
    private function getGoalData($request) 
    { 
        $user_data = array_get($request, 'data');
        $fields = array(
            'title', 
            'text', 
            'type', 
            'icon_item', 
            'icon_zero', 
            'icon_true', 
            'icon_false', 
            'unit', 
            'prepend', 
            'aggregate', 
            'template', 
            'approved', 
            'tab', 
            'position',
            'archived',
            'privacy'
        );
        $data = array();
        foreach ($fields as $field) {
            if (isset($user_data[$field])) {
                $data[$field] = $user_data[$field];
                if ($field == 'archived' && !$data[$field]) {
                    $data[$field] = null;
                }
            }
        }
        return $data;
    }
    
    private function viewGoal($id) 
    {
        $Goals = new GoalsModel;
        $fields = array(
            'id', 
            'title', 
            'text', 
            'type', 
            'icon_item', 
            'icon_zero', 
            'icon_true', 
            'icon_false', 
            'unit', 
            'prepend', 
            'aggregate', 
            'template', 
            'approved', 
            'tab', 
            'position', 
            'archived',
            'privacy',
            'user'
        );
        return $Goals->view($id, $fields);
    }
   
    public static function getIcons($data) 
    {
       
		import('model.Icons');
        $Icons = new IconsModel;
        
        $Conf = Framework::get('Conf');
        
        $new_icons = array();
        foreach (array('icon_item', 'icon_zero', 'icon_true', 'icon_false') as $icon) {
            if (isset($data[$icon]) && (preg_match('/^http:\/\//', $data[$icon]) || preg_match('/^https:\/\//', $data[$icon]))) {
                $icon_id = $Icons->select('id', SQL::quote('src = ? and user = ?', $data[$icon], $Conf->ENV->UID), null, 1);
                if (!$icon_id) {
                    $icon_data = array('src' => $data[$icon], 'user' => $Conf->ENV->UID);
                    $new_icons[$icon] = $icon_data;
                }
                $data[$icon] = 0;
            }
        }
        return $new_icons;
    }
    
    public static function addIcons($icons) 
    {
		
     // print_r($icons);
	  
		import('model.Icons');
        import('lib.image');
        $Icons = new IconsModel;
        
        $Conf = Framework::get('Conf');
        
        $data = array();
        foreach ($icons as $field => $icon) {
            // add icon
			if($icon)
			{
            $id = $Icons->add($icon);
            $icon['id'] = $id;
            $data[$field] = $id;
            
            // copy an icon to the server
			
            $file = file::get_remote($icon['src']);
            
			// as we are saving icons to a central location shall modify save path to new location//
				
				//$path = $Conf->PTH->icons . $Conf->ENV->user->login . '/' . $id . '.png';
				//$src  = $Conf->URL->icons . $Conf->ENV->user->login . '/' . $id . '.png';
				$path = $Conf->PTH->public .'img/users/'.strtotime('now').'_'.$id . '.png';
				$src  = $Conf->URL->public .'img/users/'.strtotime('now').'_'.$id . '.png';
		
            file::put_contents($path, $file);
            
            $resized = image::resize(image::from_file($path), 24, 24);
            image::save($resized, $path, IMAGETYPE_PNG);
            //print $src;
            // edit icon
			//print $id."==".$src;
            $Icons->edit($id, array('src' => $src));
            $icon['src'] = $src;
			}
        }  
        return $data;
    }
        
     // delete a goal
    public function handleDeleteGoal(array $request = array())
    {
       
		if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        
        // check owner
        if ($Goals->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        // try to edit goal
        if ($Goals->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => $Goals->errors));
        }
        
        // delete goal data
        $Data = new DataTable('data');
        $Data->delete_where(SQL::quote('goal = ?', $this->ENV->id));
        
        // delete plans
        $Plan = new DataTable('plan');
        $Plan->delete_where(SQL::quote('goal = ?', $this->ENV->id));
        
        return $this->respondOk(array('item' => $this->ENV->id));
    }
    
    // sort goals
    public function handlePutDefault(array $request = array())
    {
       
		
		if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Goals');
        $Goals = new GoalsModel;
        
        $data = array_get($request, 'data');
        $response = array();
        foreach ($data as $goal) {
            // check owner
            if ($Goals->view($goal['id'], 'user') != $this->ENV->UID) {
                continue;
            }
            $Goals->edit($goal['id'], array('position' => $goal['position']));
            $response[] = array('id' => $goal['id'], 'position' => $goal['position']);
        }
        return $this->respondOk(array('item' => $response));
    }
   
    // save data
    public function handlePostData(array $request = array())
    {
	
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Goals');
        $Goals = new GoalsModel;
        
        // check owner
        $goal = $Goals->view($this->ENV->id, array('user', 'type'));
        if ($goal['user'] != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Table = new DataTable('data');
        $condition = SQL::quote('goal = ? and date = ?', $this->ENV->id, $this->ENV->date);
        $exists = $Table->exists_where($condition);
        
        // set data
        $data = array();
        if (!$exists) {
            $data = array('goal' => $this->ENV->id, 'date' => $this->ENV->date);
        }
        if (isset($request['value'])) {
            $data['value'] = $request['value'];
        }
        if (isset($request['text'])) {
            $data['text'] = $request['text'];
        }
        $data['user'] = $this->ENV->UID;
        if ($data) {
            if ($exists) {
                $data['modified'] = date('Y-m-d H:i:s');
                $Table->update_where($data, $condition);
            } else {
                $data['created'] = date('Y-m-d H:i:s');
                $data['modified'] = date('Y-m-d H:i:s');
                $Table->insert($data);
            }
        }
        
        // timer
        if ($goal['type'] == 'timer' && isset($request['start'])) {
            $DataStart = new DataTable('data_start');
            $data = array();
            $exists = $DataStart->exists_where($condition);
            if (!$exists) {
                $data = array('goal' => $this->ENV->id, 'date' => $this->ENV->date);
            }
            $data['start'] = $request['start'] ? $request['start'] : null;
            if (!$exists) {
                $DataStart->insert($data);
            } else {
                $DataStart->update_where($data, $condition);
            }
        }
        
        return $this->respondOk(array('ok' => 'ok'));        
    }
     
    // save plan
    public function handlePostPlan(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Goals');
        $Goals = new GoalsModel;
        
        // check owner
        if ($Goals->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Table = new DataTable('plan');
        $condition = SQL::quote('goal = ? and date = ?', $this->ENV->id, $this->ENV->date);
        if ($Table->exists_where($condition)) {
            $data = array();
            if (isset($request['value'])) {
                $data['value'] = $request['value'];
            }
            if (isset($request['text'])) {
                $data['text'] = $request['text'];
            }
            if (isset($request['date'])) {
                $data['date'] = $request['date'];
            }
            $Table->update_where($data, $condition);
        } else {
            $data = array('goal' => $this->ENV->id, 'date' => $this->ENV->date);
            if (isset($request['value'])) {
                $data['value'] = $request['value'];
            }
            if (isset($request['text'])) {
                $data['text'] = $request['text'];
            }
            $Table->insert($data);
        }
        
        return $this->respondOk(array('ok' => 'ok'));        
    }
}
?>