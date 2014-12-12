<?php
class ErrorRoute extends BaseRoute
{
    public function __construct()
    {
        $this->controllers_dir = Conf::instance()->PTH->errors;
    }
    
    protected function retriveLanguage($query)
    {
        return $query;
    }
        
    public function getController($query)
    {
        return $this->includeController($query);
    }
}
?>