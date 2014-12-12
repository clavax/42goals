<?php
import('base.controller.BaseApi');
import('domains.api.v1.oauth.OAuthApi');

class MeApi extends BaseApi
{
    public function handleGetDefault(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Users = new UsersModel;
        
        $user = $Users->view($this->ENV->UID, array('id', 'login', 'name', 'email'));
        
        return $this->respondOk(array('user' => $user));
    }
}
?>