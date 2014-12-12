<?php
function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

define('DESCRIBE_RETURN', 0);
define('DESCRIBE_ECHO', 1);
define('DESCRIBE_DIE', 2);
function describe($var, $mode = 0)
{
    $describe = '<pre>' . print_r($var, 1) . '</pre>';
    switch ($mode) {
    case DESCRIBE_DIE:
        die($describe);
        break;
    case DESCRIBE_ECHO:
        echo $describe;
        break;
    default:
    case DESCRIBE_RETURN:
        return $describe;
    }
}

function array_get($array, $key, $default = null)
{
    if (is_array($array) && array_key_exists($key, $array)) {
        return $array[$key];
    } else {
        return $default;
    }
}

function get_user_var($key, $default = null, $type = null)
{
    $value = $default;
    if (array_key_exists($key, $_REQUEST)) {
        $value = $_REQUEST[$key];
    } elseif (array_key_exists($key, $_FILES)) {
        $value = $_FILES[$key];
    }

    if ($type) {
        settype($value, $type);
    }
    
    if (get_magic_quotes_gpc()) {
        if (is_scalar($value)) {
            $value = stripslashes($value);
        } elseif (is_array($value)) {
            array_walk_recursive($value, 'stripslashes');
        }
    }
    return $value;
}

function isset_user_var($key)
{
    return array_key_exists($key, $_REQUEST) || array_key_exists($key, $_FILES);
}

function import($path)
{
    return Framework::import($path);
}

function path($path, $ext = '')
{
    return file_exists($path) ? $path : Framework::transform_path($path, $ext);
}

function object($name)
{
    return Framework::get($name);
}
?>