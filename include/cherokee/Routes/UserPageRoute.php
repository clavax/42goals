<?php
import('cherokee.Routes.StaticRoute');

class UserPageRoute extends StaticRoute
{
    public function getController($query)
    {
        $parts = explode('/', $query);
        if (count($parts) <= 1) {
            return false;
        }
        if ($parts[0] != 'users') {
            return false;
        }
        
        if (in_array($parts[1], array('location')) || preg_match('/^p\d+$/', $parts[1])) {
            return false;
        }
        
        $this->ENV->username = $parts[1];
        $query = implode('/', array_slice($parts, 2));
        
        $this->controllers_dir = $this->PTH->handlers . 'user/';
        
        $params = array();
        $controller = false;
        while (!$controller && strlen($query)) {   
            if (($controller = $this->tryPath($query, $params)) !== false) {
                break;
            }
            if (strrpos($query, '/') !== false) {
                array_unshift($params, substr(strrchr($query, '/'), 1));
                $query = substr($query, 0, strrpos($query, '/'));
            } else {
                array_unshift($params, $query);
                $query = '';
            }
        }
        if (!$controller && !strlen($query)) {
            $controller = $this->tryPath('index', $params);
        }
        
        $this->ENV->query = 'users/' . $this->ENV->username . '/' . $query;
        
        
        return $controller;
    }
    
    protected function getControllerPath($query)
    {
        return arrays::first(glob($this->controllers_dir . $query . '/*.php', GLOB_NOSORT));
    }
}