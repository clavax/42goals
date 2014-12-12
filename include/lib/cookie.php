<?php
class cookie
{
    static public function set($name, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        if (function_exists('setcookie')) {
            setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        } else {
            $headers = 'Set-Cookie: ' . $name . '=' . rawurlencode($value) 
                     . (empty($domain) ? '' : '; Domain=' . $domain)
                     . (empty($expire) ? '' : '; Max-Age=' . $expire)
                     . (empty($path) ? '' : '; Path=' . $path)
                     . (!$secure ? '' : '; Secure')
                     . (!$httponly ? '' : '; HttpOnly');
    
            header($headers, false);
        }
    }
    
    static public function remove($name)
    {
        self::set($name, false, time() - 3600 * 24 * 30 * 12, '/');
    }
}
?>