<?php
class CliRoute extends BaseRoute
{
    public function __construct()
    {
        $this->controllers_dir = $this->PTH->cli;
    }
    
    protected function retriveLanguage($query)
    {
        return $query;
    }
        
    public function getController($query)
    {
    	$this->env['controller'] = $this->getControllerPath($query);
        return $this->includeController($query);
    }	
}