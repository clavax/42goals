<?php
import('base.controller.BaseApi');
import('model.Tabs');

class TabsApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'item' => '(\d+): id'
        ));
    }
    
    public function handlePostDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Tabs = new TabsModel;
        
        $data = array_get($request, 'data');
        $data['user'] = $this->ENV->UID;
        if (($id = $Tabs->add($data)) === false) {
            return $this->respondOk(array('error' => $Tabs->errors));
        }
        $data['id'] = $id;
        return $this->respondOk(array('item' => $data));
    }

    public function handlePutItem(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Tabs = new TabsModel;
        
        if ($Tabs->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $data = array_get($request, 'data');
        if ($Tabs->edit($this->ENV->id, $data) === false) {
            return $this->respondOk(array('error' => $Tabs->errors));
        }
        
        $item = $Tabs->view($this->ENV->id, array('id', 'title', 'position'));
        return $this->respondOk(array('item' => $item));
    }
    

    public function handleDeleteItem(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Tabs = new TabsModel;
        
        if ($Tabs->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        if ($Tabs->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => $Tabs->errors));
        }
        
        return $this->respondOk(array('item' => $this->ENV->id));
    }
    
    // sort tabs
    public function handlePutDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        import('model.Tabs');
        $Tabs = new TabsModel;
        
        $data = array_get($request, 'data');
        foreach ($data as $tab) {
            // check owner
            if ($Tabs->view($tab['id'], 'user') != $this->ENV->UID) {
                continue;
            }
            $Tabs->edit($tab['id'], array('position' => $tab['position']));
        }
        return $this->respondOk(array('ok' => 'ok'));        
    }
}
?>