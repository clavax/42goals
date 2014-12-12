<?php
import('base.controller.BaseApi');

class TestApi extends BaseApi 
{
    public function handleGetDefault(array $request = array())
    {
        return describe($request);
    }
    
    public function handlePostDefault(array $request = array())
    {
        $this->Error->log(describe($_SERVER));
        $this->Error->log(describe($request));
        return describe($request);
    }
}
?>