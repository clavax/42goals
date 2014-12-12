<?php
if (!function_exists('json_encode')) {
    function json_encode($var)
    {
        $string = '';
        if (is_scalar($var)) {
            if (is_string($var)) {
                $string = '"' . self::quote($var) . '"';
            } else if (is_numeric($var)) {
                $string = $var;
            } else if (is_null($var)) {
                $string = 'null';
            } else if (is_bool($var)){
                $string = $var ? 'true' : 'false';
            } else {
                $string = '""';
            }
        } else if (arrays::nonempty($var) || $var instanceof ArrayRecursiveObject) {
            if ($as_array) {
                $string = '[';
                $first = true;
                foreach ($var as $key => $value) {
                    if ($first) {
                        $first = false;
                    } else {
                        $string .= ',';
                    }
                    $string .= self::encode($value);
                }
                $string .= ']';
            } else {
                $string = '{';
                $first = true;
                foreach ($var as $key => $value) {
                    if ($first) {
                        $first = false;
                    } else {
                        $string .= ',';
                    }
                    $string .= '"' . $key . '":' . self::encode($value);
                }
                $string .= '}';
            }
        } else {
            if (is_array($var)) {
                $string = '[]';
            } else {
                $string = '""';
            }
        }
        return $string;
    }
}

class json {
    public static function encode($var, $as_array = false)
    {
        return json_encode($var, JSON_FORCE_OBJECT);
    }
    
    public static function encode_array($var)
    {
        return json_encode($var);
    }
    
    public static function quote($var)
    {
        $search = array('\\', '"', "\n", "\r", "\r\n", '/');
        $replace = array('\\\\', '\"', '\n', '\r', '\r\n', '\/');
        return str_replace($search, $replace, $var);
    }
    
    public static function decode($str) {
        return json_decode($str);
    }
}

?>
