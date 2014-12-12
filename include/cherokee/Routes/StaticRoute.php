<?php
class StaticRoute extends BaseRoute
{
    protected $controllers_dir = '';
    
    public function __construct()
    {
        $this->controllers_dir = Conf::instance()->PTH->static;
    }
        
    public function getController($query)
    {
        $params = array();
        $controller = false;
        while (!$controller && strlen($query)) {   
            if (($controller = $this->tryPath($query . '/index', $params)) !== false) {
                break;
            }
            if (($controller = $this->tryPath($query, $params)) !== false) {
                break;
            }
            if (strrpos($query, '/') !== false) {
                array_unshift($params, substr(strrchr($query, '/'), 1));
                $query = substr($query, 0, strrpos($query, '/'));
            } else {
                if (($controller = $this->tryPath('default/' . $query, $params)) !== false) {
                    break;
                } else {
                    array_unshift($params, $query);
                    $query = '';
                }
            }
        }
        if (!$controller && !strlen($query)) {
            $controller = $this->tryPath('default/index', $params);
        }
        
        return $controller;
    }
    
    protected function getControllerPath($query)
    {
        $path = parent::getControllerPath($query);
        if ($this->env['query'] == 'default/index') {
            $this->env['query'] = '';
        }
        $this->env['query'] = preg_replace('/^default\//', '', $this->env['query']);
        $this->env['query'] = preg_replace('/\/index$/', '', $this->env['query']);
        return $path;
    }
}
?>