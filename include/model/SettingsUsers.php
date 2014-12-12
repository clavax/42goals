<?php
import('model.Users');

class SettingsUsersModel extends UsersModel
{
    protected function validate(&$data, $id = false)
    {
        parent::validate($data, $id);
        unset($this->errors['password']);

        if (self::hashPassword($data['password']) != $this->view($this->ENV->UID, 'password')) {
            $this->errors['password'] = 'wrong';
        }
        
        if (isset($data['password1']) && !empty($data['password1'])) {
            if (strcmp($data['password1'], $data['password2'])) {
                $this->errors['password2'] = 'wrong';
            } else {
                $data['password'] = $data['password1'];
            }
        } else {
            unset($data['password']);
        }
        
        // check unique fields
        if (isset($data['new_email']) && !empty($data['new_email'])) {
            if ($this->count('*', SQL::quote('email ' . SQL::ILIKE . ' ?', $data['new_email']))) {
                $this->errors['new_email'] = 'not_unique';
            }
        }
        
        return empty($this->errors);
    }
    
    public function deleteAccount($password)
    {
        $this->errors = array();
        if ($this->view($this->ENV->UID, 'status') == 'active' && self::hashPassword($password) != $this->view($this->ENV->UID, 'password')) {
            $this->errors['password'] = 'wrong';
            return false;
        }
        
        return $this->delete($this->ENV->UID);
    }
}
?>