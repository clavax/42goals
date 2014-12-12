<?php
import('base.controller.BaseApi');
import('model.Categories');

class CategoriesApi extends BaseApi
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
        if (!Access::loggedIn() || !Access::isAdmin()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        $Categories = new CategoriesModel;
        
        $data = array_get($request, 'data');
        if (($id = $Categories->add($data)) === false) {
            return $this->respondOk(array('error' => $Categories->errors));
        }
        $data['id'] = $id;
        return $this->respondOk(array('item' => $data));
    }

    public function handlePutItem(array $request = array())
    {
        if (!Access::loggedIn() || !Access::isAdmin()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Categories = new CategoriesModel;
        
        $data = array_get($request, 'data');
        if ($Categories->edit($this->ENV->id, $data) === false) {
            return $this->respondOk(array('error' => $Categories->errors));
        }
        
        $item = $Categories->view($this->ENV->id, array('id', 'title_en', 'title_ru', 'title_fr', 'name', 'position'));
        return $this->respondOk(array('item' => $item));
    }
    

    public function handleDeleteItem(array $request = array())
    {
        if (!Access::loggedIn() || !Access::isAdmin()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Categories = new CategoriesModel;
        
        if ($Categories->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => $Categories->errors));
        }
        
        return $this->respondOk(array('item' => $this->ENV->id));
    }
    
    // sort tabs
    public function handlePutDefault(array $request = array())
    {
        if (!Access::loggedIn() || !Access::isAdmin()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }

        $Categories = new CategoriesModel;
        
        $data = array_get($request, 'data');
        foreach ($data as $category) {
            $Categories->edit($category['id'], array('position' => $category['position']));
        }
        return $this->respondOk(array('ok' => 'ok'));        
    }
}
?>