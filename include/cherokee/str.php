<?php
class str
{
    const ENCODING = 'UTF-8';
    
    public static function split($pattern, $string)
    {
        if (function_exists('mb_split')) {
            return mb_split($pattern, $string);
        } else {
            return split($pattern, utf8_decode($string));
        }
    }

    public static function len($string)
    {
        $length = 0;
        if (function_exists('mb_strlen')) {
            $length = mb_strlen($string, str::ENCODING);
        } else {
            $length = strlen($string); 
        }
        return $length;
    }
    

    public static function sub($string, $start, $length = null)
    {
        $sub = '';
        if (!isset($length)) {
            $length = str::len($string);
        }
        if (function_exists('mb_substr')) {
            $sub = mb_substr($string, $start, $length, str::ENCODING);
        } else {
            $sub = substr($string, $start, $length); 
        }
        return $sub;
    }
    
    public static function tolower($string)
    {
        if (function_exists('mb_strtolower')) {
            $string = mb_strtolower($string, str::ENCODING);
        } else {
            $string = strtolower($string);
        }
        return $string;
    }

    public static function toupper($string)
    {
        if (function_exists('mb_strtoupper')) {
            $string = mb_strtoupper($string, str::ENCODING);
        } else {
            $string = strtoupper($string);
        }
        return $string;
    }

    public static function ucwords($string)
    {
        if (function_exists('mb_convert_case')) {
            $string = mb_convert_case($string, MB_CASE_TITLE, str::ENCODING);
        } else {
            $string = ucwords($string);
        }
        return $string;
    }

    public static function ucfirst($string)
    {
        if (function_exists('mb_strtoupper')) {
            $string = str::toupper(str::sub($string, 0, 1)) . str::sub($string, 1);
        } else {
            $string = ucfirst($string);
        }
        return $string;
    }
    
    public static function ord($c)
    {
        $h = ord($c{0});
        if ($h <= 0x7F) {
            return $h;
        } else if ($h < 0xC2) {
            return false;
        } else if ($h <= 0xDF) {
            return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
        } else if ($h <= 0xEF) {
            return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6
                                     | (ord($c{2}) & 0x3F);
        } else if ($h <= 0xF4) {
            return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12
                                     | (ord($c{2}) & 0x3F) << 6
                                     | (ord($c{3}) & 0x3F);
        } else {
            return false;
        }
    }
    
    public static function toUnicode($string)
    {
        $out = '';
        for ($i = 0; $i < str::len($string); $i ++) {
            $char = str::sub($string, $i, 1);
            $ord = (string) dechex(str::ord($char));
            if (strlen($ord) < 4) {
                $ord = str_pad($ord, 4, '0', STR_PAD_LEFT);
            }
            $out .= $ord;
        }
        return $out;
    }    
}
?>