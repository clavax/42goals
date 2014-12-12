<?php
abstract class BaseController extends Object
{
    protected $rules = array();
    protected $status;
    
    protected function addRule($rules)
    {
        foreach ($rules as $action => $rule) {
            $this->rules[$action] = $rule;
        }
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function checkParameters($params)
    {
        $env = array();
        $query = implode('/', $params);
        if (!strlen($query)) {
            $env['action'] = BaseRoute::DEFAULT_ACTION;
            return $env;
        }
        
        if (empty($this->rules)) {
            return false;
        }

        $matches = array();
        foreach ($this->rules as $action => $rule) {
            if (preg_match('#^\s*(.+)\s*:\s*((?:[a-z_][a-z0-9_]*)(?:/[a-z_][a-z0-9_]*)*)\s*$#i', $rule, $matches)) {
                $pattern   = trim($matches[1]);
                $variables = explode('/', trim($matches[2]));

                if (preg_match('#^' . $pattern . '$#', $query, $matches)) {
                    array_shift($matches);
                    foreach ($matches as $key => $match) {
                        if (isset($variables[$key])) {
                            $env[$variables[$key]] = $match;
                        }
                    }
                    $env['action'] = $action;
                    return $env;
                }
            }
        }
        
        return false;
    } 
}
?>