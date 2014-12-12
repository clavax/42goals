<?php
import('base.controller.BaseApi');
import('static.api.goals.GoalsApi');

class TemplatesApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(
            array(
                'template' => '(\d+): id',
                'search' => '(search): null',
            )
        );
    }
    
    // add a new template
    public function handlePostDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Templates');
        $Templates = new TemplatesModel;
        
        $this->Conf->loadLanguage('goals');
                
        // get data
        $data = $this->getTemplateData($request);
        $data['user'] = $this->ENV->UID;
        
        // get icons
        $new_icons = GoalsApi::getIcons($data);
        
        // try to add new template
        if (($id = $Templates->add($data)) === false) {
            $errors = $Templates->errors;
            
            foreach ($errors as $field => &$error) {
                $error = $this->LNG->get("Error_{$error}_{$field}", "Error_{$error}_{$field}");
            }
            
            return $this->respondOk(array('error' => $errors));
        }
        
        // add icons
        $data = GoalsApi::addIcons($new_icons);
        
        // update template
        $Templates->edit($id, $data);
        
        $item = $this->viewTemplate($id);
        return $this->respondOk(array('item' => $item, 'icon' => array_values($new_icons)));
    }
    
    // edit a template
    public function handlePutTemplate(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        import('model.Templates');
        import('model.Icons');
        $Templates = new TemplatesModel;
        $Icons = new IconsModel;
        
        $this->Conf->loadLanguage('goals');
        
        // check owner
        if ($Templates->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }

        // get data
        $data = $this->getTemplateData($request);
    
        // get icons
        $new_icons = GoalsApi::getIcons($data);

        // try to edit the goal
        if ($Templates->edit($this->ENV->id, $data) === false) {
            $errors = $Templates->errors;
            
            foreach ($errors as $field => &$error) {
                $error = $this->LNG->get("Error_{$error}_{$field}", "Error_{$error}_{$field}");
            }
            
            return $this->respondOk(array('error' => $errors));
        }
        
        // add icons
        $data = GoalsApi::addIcons($new_icons);
        
        // update goal
        $Templates->edit($this->ENV->id, $data);
        
        $item = $this->viewTemplate($this->ENV->id);
        return $this->respondOk(array('item' => $item, 'icon' => array_values($new_icons)));        
    }
    
    private function getTemplateData($request) 
    {
        $user_data = array_get($request, 'data');
        $fields = array(
            'title', 
            'name', 
            'preview', 
            'description', 
            'instructions', 
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
            'position',
            'category',
        );
        $data = array();
        foreach ($fields as $field) {
            if (isset($user_data[$field])) {
                $data[$field] = $user_data[$field];
            }
        }
        return $data;
    }
    
    private function viewTemplate($id) 
    {
        $Templates = new TemplatesModel;
        $fields = array(
            'id', 
            'title', 
            'name', 
            'preview', 
            'description', 
            'instructions', 
            'type', 
            'icon_item', 
            'icon_zero', 
            'icon_true', 
            'icon_false', 
            'unit', 
            'prepend', 
            'aggregate', 
            'approved', 
            'position', 
            'category', 
        );
        return $Templates->view($id, $fields);
    }
    
    private function getIcons(&$data) 
    {
        import('model.Icons');
        $Icons = new IconsModel;
        
        $new_icons = array();
        foreach (array('icon_item', 'icon_zero', 'icon_true', 'icon_false') as $icon) {
            if (isset($data[$icon]) && preg_match('/^http:\/\//', $data[$icon])) {
                $icon_id = $Icons->select('id', SQL::quote('src = ? and user = ?', $data[$icon], $this->ENV->UID), null, 1);
                if (!$icon_id) {
                    $icon_data = array('src' => $data[$icon], 'user' => $this->ENV->UID);
                    $new_icons[$icon] = $icon_data;
                }
                $data[$icon] = 0;
            }
        }
        return $new_icons;
    }
    
    private function addIcons(&$icons) 
    {
        import('model.Icons');
        $Icons = new IconsModel;
        
        $data = array();
        foreach ($icons as $field => &$icon) {
            // add icon
            $id = $Icons->add($icon);
            $icon['id'] = $id;
            $data[$field] = $id;
            
            // copy an icon to the server
            $file = file::get_remote($icon['src']);
            $path = $this->PTH->icons . $this->ENV->user->login . '/' . $id . '.png';
            $src  = $this->URL->icons . $this->ENV->user->login . '/' . $id . '.png';
            file::put_contents($path, $file);
            
            GoalsApi::resizeIcon($path, 24, 24);
            
            // edit icon
            $Icons->edit($id, array('src' => $src));
            $icon['src'] = $src;
        }        
        return $data;
    }
    
    // delete a goal
    public function handleDeleteTemplate(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        import('model.Templates');
        $Templates = new TemplatesModel;
        
        // check owner
        if ($Templates->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        // try to edit goal
        if ($Templates->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => $Templates->errors));
        }
        
        return $this->respondOk(array('item' => $this->ENV->id));
    }
    
    // sort goals
    public function handlePutDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Templates');
        $Templates = new TemplatesModel;
        
        $data = array_get($request, 'data');
        $response = array();
        foreach ($data as $goal) {
            // check owner
            if ($Templates->view($goal['id'], 'user') != $this->ENV->UID) {
                continue;
            }
            $Templates->edit($goal['id'], array('position' => $goal['position']));
            $response[] = array('id' => $goal['id'], 'position' => $goal['position']);
        }
        return $this->respondOk(array('item' => $response));
    }
        
    public function handleGetSearch($request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        $query = array_get($request, 'q');
        if (strlen($query) < 3) {
            return $this->respondOk(array('error' => 'invalid query ' . $query));
        }

        import('model.Templates');
        $Templates = new TemplatesModel;

        $fields = array('id', 'title', 'text', 'type', 'icon_item', 'icon_true', 'icon_false', 'unit', 'prepend', 'aggregate');
        $search = SQL::quote('template = ?yes and (title like ?q or text like ?q)', array('yes' => 'yes', 'q' => '%' . $query . '%'));
        $limit = 10;
        $templates = $Templates->select($fields, $search, 'id', $limit);
        
        return $this->respondOk(array('item' => $templates));
    }
}
