<?php
import('base.controller.BaseApi');

abstract class CommonApi extends BaseApi
{
    protected $model;
    
    public function __construct()
    {
        parent::__construct();

        $this->addRule(
            array(
                'item' => '(\d+|new): id'
            )
        );
    }

    public function translateError($errors)
    {
        foreach ($errors as $field => &$error) {
            $key = "Error_{$error}_{$field}";
            $error = $this->LNG->has($key) ? $this->LNG->get($key) : $key;
        }
        
        return $errors;
    }
    
    public function handleGetItem($request)
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }

        $fields = array_get($request, 'fields', '*');
        $item = $this->model->view($this->ENV->id, $fields);
        return $this->respondOk(array('item' => $item));
    }

    public function handlePostItem($request)
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $data = array_get($request, 'data');

        if (($id = $this->model->add($data)) === false) {
            $errors = $this->translateError($this->model->errors);
            return $this->respondOk(array('error' => $errors));
        }

        $fields = array_keys($data);
        $item = $this->model->view($id, $fields);
        $item['id'] = $id;
        return $this->respondOk(array('item' => $item));
    }
        
    public function handlePutItem($request)
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $data = array_get($request, 'data');
        $id   = $this->ENV->id;
        
        if ($this->model->edit($id, $data) === false) {
            $errors = $this->translateError($this->model->errors);
            return $this->respondOk(array('error' => $errors));
        }

        $fields = array_keys($data);
        $item = $this->model->view($id, $fields);
        $item['id'] = $id;
        return $this->respondOk(array('item' => $item));
    }

    public function handleDeleteItem()
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $id = $this->ENV->id;
        
        if ($this->model->delete($id) === false) {
            $errors = $this->translateError($this->model->errors);
            return $this->respondOk(array('error' => $errors));
        }

        return $this->respondOk(array('id' => $id));
    }
    
}
?>