<?php
import('base.model.BaseModel');
import('lib.validate');

class UsersModel extends BaseModel
{
    const SALT = 'pepper';
    
    public function __construct()
    {
        parent::__construct('users');
    }
    protected function distribute(&$data)
    {
        if (isset($data['password'])) {
            $data['password'] = self::hashPassword($data['password']);
        }
        parent::distribute($data);
    }
    
    public static function genConfirmationCode()
    {
        return md5(rand());
    }
    
    public function confirm($code, array $data = array())
    {
        $id = $this->select('id', SQL::quote('% = ?', 'email_confirmation', $code), null, 1);
        $confirmed = false;
        if ($id) {
            $data += array('email_confirmation' => '');
            $confirmed = $this->edit($id, $data);
        }
        return $confirmed ? $id : false;
    }
    
    public static function validateLoginLength($login, $min_len, $max_len)
    {
        return strlen($login) >= $min_len && strlen($login) <= $max_len;
    }
    
    public static function validateLogin($login)
    {
        return preg_match('/^([a-z0-9]+[-_]?)*[a-z0-9]$/i', $login);
    }
    
    public static function hashPassword($str)
    {
        return md5(self::SALT . $str);
    }
}
?>