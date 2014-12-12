<?php
import('cherokee.DB.common.SQL_Query');

class Common_SQL_Select extends SQL_Query
{
    protected $fields;
    protected $tables;
    protected $limit;
    protected $offset;
    protected $condition;
    protected $post_condition;
    protected $orders;
    protected $groups;
    protected $joints;

    public function __construct($fields, $tables)
    {
        $this->fields = $this->get_name_set($fields);
        $this->tables = $this->get_name_set($tables);
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }
    
    public function build()
    {
        $query = 'select';

        // fields
        if (isset($this->fields) && !empty($this->fields)) {
            $first = true;
            foreach ($this->fields as $field) {
                if (count($this->tables) == 1) {
                    $table = arrays::first($this->tables);
                    $table = SQL::quote_name(isset($table['as']) ? $table['as'] : $table['name']) . '.';
                } else {
                    $table = '';
                }
                $query .= (!$first ? ', ' : ' ') . ($field['name'] == '*' || SQL::is_name($field['name']) ? $table : '') . SQL::quote_name($field['name']) . (isset($field['as']) ? ' as ' . SQL::quote_name($field['as']) : '');
                $first = false;
            }

            // add fields from JOIN statement
            if (isset($this->joints) && !empty($this->joints)) {
                foreach ($this->joints as $join) {
                    $table = SQL::quote_name(isset($join['table']['as']) ? $join['table']['as'] : $join['table']['name']);
                    if (isset($join['fields']) && !empty($join['fields'])) {
                        foreach ($join['fields'] as $field) {
                            $query .= ', ' . ($field['name'] == '*' || SQL::is_name($field['name']) ? $table . '.' : '') . SQL::quote_name($field['name']) . (isset($field['as']) ? ' as ' . SQL::quote_name($field['as']) : '');
                        }
                    }
                }
            }
        }

        // FROM statement
        if (isset($this->tables) && !empty($this->tables)) {
            $query .= ' from';
            $first = true;
            foreach ($this->tables as $table) {
                $query .= (!$first ? ', ' : ' ') . SQL::quote_name($table['name']) . (isset($table['as']) ? ' as ' . SQL::quote_name($table['as']) : '');
                $first = false;
            }
        }

        // add JOINs
        if (isset($this->joints) && !empty($this->joints)) {
            foreach ($this->joints as $join) {
                $query .= "\n" . ' left join ' . SQL::quote_name($join['table']['name']) . (isset($join['table']['as']) ? ' as ' . SQL::quote_name($join['table']['as']) : '') . ' on (' . $join['condition'] .')';
            }
        }

        // add WHERE statement
        if ($this->condition) {
            $query .= ' where ' . $this->condition;
        }

        // add GROUP BY statement
        if (isset($this->groups) && !empty($this->groups)) {
            $query .= ' group by';
            $first = true;
            foreach ($this->groups as $field) {
                $query .= (!$first ? ', ' : ' ') . SQL::quote_name($field);
                $first = false;
            }
        }

        // add HAVING statement
        if ($this->post_condition) {
            $query .= ' having ' . $this->post_condition;
        }

        // add ORDER BY statement
        if (isset($this->orders) && !empty($this->orders)) {
            if (is_array($this->orders)) {
                $query .= ' order by';
                $first = true;
                foreach ($this->orders as $order) {
                    $query .= (!$first ? ',' : '') . ' ' . SQL::quote_name($order['field']) . (isset($order['dir']) ? ' ' . $order['dir'] : '');
                    $first = false;
                }
            }
        }

        // add LIMIT statement
        if (isset($this->limit)) {
            $query .= ' limit';
            if (isset($this->offset)) {
                $query .= ' ' . $this->limit . ' offset ' . $this->offset;
            } else {
                $query .= ' ' . $this->limit;
            }
        }

        return $query;
    }

    protected function get_name($name)
    {
        $result = array();

        if (is_array($name)) {
            list($result['as'], $result['name']) = each($name);
        } else if (is_string($name)) {
            $result['name'] = $name;
        } else if ($name instanceof DataTable) {
            $name = $name->__toString();
        } else {
            throw new Exception('Unsupported parameter type');
        }

        return $result;
    }

    protected function get_name_set($names)
    {
        $result = array();

        if (is_array($names)) {
            $j = 0;
            foreach ($names as $i => $name) {
                if ($name instanceof DataTable) {
                    $name = $name->__toString();
                }
                if (is_int($i)) {
                    $result[$j] = array('name' => $name);
                } else {
                    $result[$j] = array('as' => $i, 'name' => $name);
                }
                $j ++;
            }
        } else if (is_string($names)) {
            $result = array(array('name' => $names));
        } else if ($names instanceof DataTable) {
            $result = $names->__toString();
        } else {
            throw new Exception('Unsupported parameter type');
        }

        return $result;
    }

    public function where($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    public function and_where($condition)
    {
        if (isset($this->condition)) {
            $this->where('(' . $this->condition . ') and ' . $condition);
        } else {
            $this->where($condition);
        }
        return $this;
    }

    public function or_where($condition)
    {
        if (isset($this->condition)) {
            $this->where('(' . $this->condition . ') or ' . $condition);
        } else {
            $this->where($condition);
        }
        return $this;
    }

    public function limit($offset, $limit = null)
    {
        if (isset($limit)) {
            $this->limit = $limit;
            $this->offset = $offset;
        } else {
            $this->limit = $offset;
        }
        return $this;
    }

    public function order($field, $dir = null)
    {
        if (isset($dir)) {
            $this->orders[] = array('field' => $field, 'dir' => $dir);
        } else {
            $this->orders[] = array('field' => $field);
        }
        return $this;
    }

    public function group($field)
    {
        $this->groups[] = $field;
        return $this;
    }

    public function having($condition)
    {
        $this->post_condition = $condition;
        return $this;
    }

    public function join($table, $condition, $fields = null)
    {
        $joint = array('table' => $this->get_name($table), 'condition' => $condition);
        if (isset($fields)) {
            $joint['fields'] = $this->get_name_set($fields);
        }
        $this->joints[] = $joint;
        return $this;
    }
}
?>