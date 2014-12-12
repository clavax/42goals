<?php
import('base.controller.BaseApi');
import('domains.api.v1.oauth.OAuthApi');

class IconsApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'icon' => '(\d+): id'
        ));
    }
    
    public function handleGetDefault(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        import('model.Icons');
        $Icons = new IconsModel;
        
        $icons = $Icons->select(array('id', 'src', 'user'), SQL::quote('user = ? or user = 1', $this->ENV->UID), array('position', 'id'));
        foreach ($icons as &$icon) {
            if (!preg_match('/^http:\/\//', $icon['src'])) {
                $icon['src'] = $this->CNF->site->host . $icon['src'];
            }
            $icon['editable'] = $icon['user'] == $this->ENV->UID;
            unset($icon['user']);
        }
        
        return $this->respondOk(array('icon' => $icons));
    }
    
    public function handleGetIcon(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        import('model.Icons');
        $Icons = new IconsModel;
        
        $icon = $Icons->view($this->ENV->id, array('id', 'src', 'user'));
        if ($icon['user'] != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        $icon['editable'] = $icon['user'] == $this->ENV->UID;
        unset($icon['user']);
        
        $icon['src'] = $this->CNF->site->host . $icon['src'];
        
        return $this->respondOk(array('icon' => $icon));
    }
    
    public function handleDeleteIcon(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        import('model.Icons');
        $Icons = new IconsModel;
        
        $icon = $Icons->view($this->ENV->id, array('src', 'user'));
        if ($icon['user'] != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        if (!$Icons->delete($this->ENV->id)) {
            return $this->respondOk(array('error' => 'sql error'));
        }
        file::delete($this->PTH->main . $icon['src']);
        
        return $this->respondOk(array('ok' => 'ok'));
    }
    
    public function handlePostDefault(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        import('model.Icons');
        $Icons = new IconsModel;
        
        $data = array(
            'user' => $this->ENV->UID,
            'src' => '',
            'position' => 0
        );
        
        // insert into database
        if (($id = $Icons->add($data)) === false) {
            return $this->respondOk(array('error' => $Icons->errors));
        }
        
        // get data
        $content = $request['image'];
        $name    = $request['name'];
        
        // get user login
        $Users = new UsersModel;
        $login = $Users->view($this->ENV->UID, 'login');
        
        // write image
        //$filename = $login . '/' . $id . '.' . file::get_ext($name);
        $filename = $login . '/' . $id . '.png';
        if (!file::put_contents($this->PTH->icons . $filename, base64_decode($content))) {
            return $this->respondOk(array('error' => 'cannot write file'));
        }
        import('static.api.goals.GoalsApi');
        GoalsApi::resizeIcon($this->PTH->icons . $filename, 24, 24);

        $data = array('src' => $this->URL->icons . $filename);
        if (($Icons->edit($id, $data)) === false) {
            return $this->respondOk(array('error' => $Icons->errors));
        }
        
        $icon = $Icons->view($id, array('id', 'src'));        
        
        return $this->respondOk(array('icon' => $icon));
    }
}
?>