<?php
import('base.controller.BaseApi');
import('model.Charts');

class ChartsApi extends BaseApi
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
        $Charts = new ChartsModel;
        
        $data = array_get($request, 'data');
        $data['user'] = $this->ENV->UID;
        if (($id = $Charts->add($data)) === false) {
            return $this->respondOk(array('error' => $Charts->errors));
        }
        $data['id'] = $id;
        return $this->respondOk(array('item' => $data));
    }

    public function handlePutItem(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Charts = new ChartsModel;
        
        $data = array_get($request, 'data');
        if ($Charts->edit($this->ENV->id, $data) === false) {
            return $this->respondOk(array('error' => $Charts->errors));
        }
        
        $item = $Charts->view($this->ENV->id);
        return $this->respondOk(array('item' => $item));
    }
    

    public function handleDeleteItem(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Charts = new ChartsModel;
        
        if ($Charts->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => $Charts->errors));
        }
        
        return $this->respondOk(array('item' => $this->ENV->id));
    }
}
?>