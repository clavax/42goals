<?php
import('model.Users');
import('static.system.captcha.Captcha');

class RegistrationUsersModel extends UsersModel
{
    public function add($data)
    {
        if (!isset($data['timezone'])) {
            $data['timezone'] = 0;
        }
        $data['registered'] = gmdate('Y-m-d', time() + $data['timezone'] * 3600);
        $data['language']   = $this->ENV->language;
        return parent::add($data);
    }
    
    protected function validate(&$data, $id = false)
    {
        parent::validate($data, $id);
        
        if (!isset($this->errors['password']) && (!isset($data['password2']) || strcmp($data['password'], $data['password2']))) {
            $this->errors['password2'] = 'wrong';
        }
        
        if (!isset($data['captcha']) || !Captcha::checkId($data['captcha'])) {
            Captcha::genId();
            $this->errors['captcha'] = 'wrong';
        }
        
        return empty($this->errors);
    }
}
?>