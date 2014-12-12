<?php
class validate {
    public static function email($email) {
        return preg_match('/^(?:[a-z0-9_-]+(?:\.|\+))*[a-z0-9_-]+@(?:[a-z0-9_-]+\.)*[a-z]{2,6}$/i', $email);
    }
    
    public static function time($str) {
        $m = array();
        if (!preg_match('/^(\d{1,2})[-:\.](\d{1,2})(?:[-:\.](\d{1,2}))?$/', $str, $m)) {
            return false;
        } else {
            // validate hours
            $hrs = intval($m[1]);
            if ($hrs < 0 || $hrs > 24) {
                return false;
            }

            // validate minutes
            $min = intval($m[2]);
            if ($min < 0 || $min > 59) {
                return false;
            }

            // validate seconds
            if (isset($m[3])) {
                $sec = intval($m[3]);
                if ($sec < 0 || $sec > 59) {
                    return false;
                }
            }
        }
        
        return true;
    }

    public static function date($str) {
        $m = array();
        if (!preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $str, $m)) {
            return false;
        } else {
            return checkdate($m[2], $m[3], $m[1]);
        }
    }

    public static function icq($str) {
        return preg_match('/\d{3,12}/', $str);
    }

    public static function yahoo($str) {
        return preg_match('/\w+(?:\.\w+)?/', $str);
    }

    public static function phone($str) {
        return preg_match('/\d+/', $str);
    }

    public static function lj($str) {
        return true; // @todo add checking here
    }
    
    public static function color($str)
    {
        return preg_match('/^[a-f0-9]{6}$/i', $str);
    }
}
?>