<?php
import('cherokee.DB.common.*');

class SQL extends Common_SQL
{
    const JOIN_LEFT = 'left';
    const ILIKE = 'like';
    const NULL = 'NULL';

    /**
     * Escape string
     *
     * @param string $string
     * @return string
     */
    public static function escape($string)
    {
        // @todo: why do we need to connect db?
        $db = Framework::get('db');
        return mysql_real_escape_string($string, $db->get_link());
    }

    public static function quote_value($value)
    {
        if (is_null($value)) {
            $value = self::NULL;
        } else if (!is_numeric($value) || is_string($value)) {
            $value = "'" . self::escape($value) . "'";
        }
        return $value;
    }

    public static function quote_name($name)
    {
        if (self::is_name($name)) {
            return "`" . $name . "`";
        } else {
            return $name;
        }
    }

    public static function is_name($name)
    {
        return preg_match('/^[a-z_][a-z_0-9]*$/i', $name);
    }
}
?>