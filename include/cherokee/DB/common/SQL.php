<?php
abstract class Common_SQL
{
    public static function select($fields, $table)
    {
        return new SQL_Select($fields, $table);
    }

    public static function insert(array $data, $table)
    {
        return new SQL_Insert($data, $table);
    }

    public static function update($table, array $data, $condition = null)
    {
        return new SQL_Update($table, $data, $condition);
    }

    public static function delete($table, $condition = null)
    {
        return new SQL_Delete($table, $condition);
    }

    public static function quote($query, $args = array())
    {
        if ($query instanceof SQL_Query) {
            $query = $query->__toString();
        }
        $result = '';

        if (func_num_args() == 2 && is_array($args) && !arrays::keys_int($args)) {

            $pos = 0; // current char position in query
            $values = array(); // cached values
            $names  = array(); // cached names

            while (isset($query[$pos])) {
                switch ($query[$pos]) {
                case '?':
                    $name = SQL::get_argument_name($query, $pos);
                    if (isset($args[$name])) {
                        if (!isset($values[$name])) {
                            if (is_scalar($args[$name]) || is_null($args[$name])) {
                                $arg = SQL::quote_value($args[$name]);
                            } else if (is_array($args[$name])) {
                                foreach ($args[$name] as &$value) {
                                    $value = SQL::quote_value($value);
                                }
                                $arg = implode(', ', $args[$name]);
                            } else {
                                throw new Exception('Unsupported type of argument');
                            }
                            $values[$name] = $arg;
                        }
                        $result .= $values[$name];
                    } else {
                        $result .= SQL::quote_value(null);
                    }
                    break;

                case '%':
                    if ($query[$pos + 1] == '%') {
                        $result .= '%';
                        $pos ++;
                        break;
                    }
                    $name = SQL::get_argument_name($query, $pos);
                    if (isset($args[$name])) {
                        if (!isset($names[$name])) {
                            if (is_scalar($args[$name]) || is_null($args[$name])) {
                                $arg = SQL::quote_name($args[$name]);
                            } else if (is_array($args[$name])) {
                                foreach ($args[$name] as &$value) {
                                    $value = SQL::quote_name($value);
                                }
                                $arg = implode(', ', $args[$name]);
                            } else {
                                throw new Exception('Unsupported type of argument');
                            }
                            $names[$name] = $arg;
                        }
                        $result .= $names[$name];
                    }
                    break;

                default:
                    $result .= $query[$pos];
                }
                $pos ++;
            }
        } else {
            $args = func_get_args();
            $pos = 0; // current char position in query
            $cur = 1; // current argument

            while (isset($query[$pos])) {
                switch ($query[$pos]) {
                case '?':
                    if (!isset($args[$cur])) {
                        $result .= SQL::quote_value(null);
                        break;
                    }
                    if (is_scalar($args[$cur]) || is_null($args[$cur])) {
                        $arg = SQL::quote_value($args[$cur]);
                    } else if (is_array($args[$cur])) {
                        foreach ($args[$cur] as &$value) {
                            $value = SQL::quote_value($value);
                        }
                        $arg = implode(', ', $args[$cur]);
                    } else {
                        throw new Exception('Unsupported type of argument');
                    }
                    $result .= $arg;
                    $cur ++;
                    break;

                case '%':
                    if (!isset($args[$cur])) {
                        $result .= '%';
                        break;
                    }
                    if (isset($query[$pos + 1]) && $query[$pos + 1] == '%') {
                        $result .= '%';
                        $pos ++;
                        break;
                    }
                    if (is_scalar($args[$cur]) || is_null($args[$cur])) {
                        $arg = SQL::quote_name($args[$cur]);
                    } else if (is_array($args[$cur])) {
                        foreach ($args[$cur] as &$value) {
                            $value = SQL::quote_name($value);
                        }
                        $arg = implode(', ', $args[$cur]);
                    } else {
                        throw new Exception('Unsupported type of argument');
                    }
                    $result .= $arg;
                    $cur ++;
                    break;

                default:
                    $result .= $query[$pos];
                }
                $pos ++;
            }
        }
        return $result;
    }

    private static function get_argument_name($query, &$pos)
    {
        $name = '';
        while (isset($query[$pos])) {
            $pos ++;
            if ($query[$pos] == '_' || ctype_alnum($query[$pos])) {
                $name .= $query[$pos];
            } else {
                $pos --;
                break;
            }
        }
        return $name;
    }

    //abstract public static function escape($string);

    //abstract public static function quote_value($value);

    //abstract public static function quote_name($name);

    //abstract public static function is_name($name);
}
?>