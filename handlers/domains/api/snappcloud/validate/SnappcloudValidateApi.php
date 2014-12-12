<?php
import('base.controller.BaseApi');

class SnappcloudValidateApi extends BaseApi 
{
    public function handlePostDefault(array $request = array())
    {
        $Users = new PrimaryTable('users', 'id');
        if ($Users->count('*', SQL::quote('email = ?', $request['email']))) {
            $response = array('Order' => array('IsValidated' => 'False'));
        } else {
            $response = array('Order' => array('IsValidated' => 'True'));
        }
        
        $this->Error->log(describe($_SERVER));
        $this->Error->log(describe($request));
        return $this->respondOk($response);
    }
}
?>