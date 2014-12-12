<?php
class arrays 
{
    public static function first($array)
    {
        return reset($array);
    }
    
    public static function last(&$array)
    {
        if (next($array) !== false) {
            prev($array);
            return false;
        } else {
            return true;
        }
    }
    
    public static function last_elem($array, $element)
    {
        $reverse = array_reverse($array);
        return $element === reset($reverse);
    }
    
    public static function last_key($array, $key)
    {
        $reverse = array_reverse($array);
        reset($reverse);
        return $key === key($reverse);
    }
    
    public static function nonempty($array)
    {
        return is_array($array) && count($array);
    }
    
    public static function combine($keys, $values) {
        foreach ($keys as $key) {
            $out[$key] = array_shift($values);
        }
        return $out;
    }
    
    public static function aslice($array, $offset, $length)
    {
        $i = 0;
        $slice = array();
        reset($array);
        while (current($array)) {
            if ($i == $offset) {
                while (($value = current($array)) && $i < $offset + $length) {
                    $slice[key($array)] = $value;
                    next($array);
                    $i ++;
                }
                break;
            }
            next($array);
            $i ++;
        }
        foreach ($array as $key => $value) {
            if ($i == $offset) {
                $slice[$key] = $value;
            }
            $i ++;
        }
        return $slice;
    }
    
    public static function divide($array, $number)
    {
        $length = ceil(count($array) / $number);
        $chunks = array();
        for ($i = 0; $i < $number; $i ++) {
            $chunks[] = array_slice($array, $i * $length, $length, true);
        }
        return $chunks;
    }
    
    public static function sort_by_field(&$array, $field, $dir = SORT_ASC)
    {
        $compare = new array_compare($field, $dir);
        usort($array, array(&$compare, 'compare'));
    }
    
    public static function keys_int($array)
    {
        foreach ($array as $key => $value) {
            if (!is_int($key)) {
                return false;
            }
        }
        return true;
    }
    
    public static function by_field($array, $field)
    {
        $array_by_field = array();
    
        foreach ($array as $element) {
            if (!isset($element[$field])) {
                return array();
            }
            $array_by_field[$element[$field]] = $element;
        }
    
        return $array_by_field;
    }
    
    public static function group_by_field($array, $field)
    {
        $group_by_field = array();
    
        foreach ($array as $element) {
            if (!isset($element[$field])) {
                return array();
            }
            $group_by_field[$element[$field]][] = $element;
        }
    
        return $group_by_field;
    }
    
    public static function list_fields($array, $field)
    {
        $fields = array();
        foreach ($array as $element) {
            if (!isset($element[$field])) {
                return array();
            }
            $fields[] = $element[$field];
        }
    
        return $fields;
    }
    
    public static function map($array, $key, $value)
    {
        $map = array();
        foreach ($array as $element) {
            if (!isset($element[$key]) || !isset($element[$value])) {
                continue;
            }
            $map[$element[$key]] = $element[$value];
        }
    
        return $map;
    }
    
    public static function add_field(&$array, $name, $value)
    {
        foreach ($array as &$elem) {
            $elem[$name] = $value;
        }
    }
}
class array_compare
{
    private $field;
    private $dir;

    public function __construct($field, $dir)
    {
        $this->field = $field;
        $this->dir = $dir;

    }

    function compare($a, $b)
    {
        return (array_get($a, $this->field) > array_get($b, $this->field) ? 1 : -1) * (pow(-1, $this->dir == SORT_DESC));
    }
}
?>