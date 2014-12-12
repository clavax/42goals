<?php
class DataTable extends Object
{
    protected $name;
    protected $fields;

    public function __construct($name)
    {
        $this->name = $name;
        $this->fields = array();
    }

    public function __get($name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : parent::__get($name);
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Insert row into table
     *
     * @param array $data
     * @return mixed
     */
    public function insert($data)
    {
        if ($this->db->query(SQL::insert($data, $this->name)->__toString())) {
            $inserted = $this->db->insert_id();
            return $inserted ? $inserted : true;
        } else {
            return false;
        }
    }

    /**
     * Update data in table
     *
     * @param array $data
     * @param string $where
     * @return mixed
     */
    public function update_where(array $data, $condition)
    {
        $query = SQL::update($this->name, $data, $condition);
        if ($this->db->query($query)) {
            //return $this->db->affected_rows();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete row from table
     *
     * @param string $where
     * @return mixed
     */
    public function delete_where($condition)
    {
        if ($this->db->query(SQL::delete($this->name, $condition)->__toString())) {
            return $this->db->affected_rows();
        } else {
            return false;
        }
    }

    /**
     * Check if row exists
     *
     * @param string $where
     * @return integer
     */
    public function exists_where($where)
    {
        return (bool)$this->count('*', $where);
    }

    /**
     * perform select query
     *
     * [array | *]   [string]   [[array] | [string]]   [[int] [int]]
     *   fields      condition       order by             limit
     *
     * @return mixed
     */
    public function select($fields = '*', $condition = null, $order = null, $paging = null, $grouping = null, $having = null)
    {
        $select = SQL::select($fields, $this->name);
        if (isset($condition)) {
            $select->where($condition);
        }
        if (isset($order)) {
            if (is_scalar($order)) {
                if (strpos($order, ':')) {
                    list($order, $dir) = explode(':', $order, 2);
                    $select->order($order, $dir);
                } else {
                    $select->order($order);
                }
            } else if (is_array($order)) {
                foreach ($order as $an_order) {
                    if (strpos($an_order, ':')) {
                        list($an_order, $dir) = explode(':', $an_order, 2);
                        $select->order($an_order, $dir);
                    } else {
                        $select->order($an_order);
                    }
                }
            }
        }
        if (isset($paging)) {
            if (strpos($paging, ':')) {
                list($start, $limit) = explode(':', $paging, 2);
                $select->limit($start, $limit);
            } else {
                $limit = $paging;
                $select->limit($paging);
            }
        }
        
        if (isset($grouping)) {
            $select->group($grouping);
        }
        
        if (isset($having)) {
            $select->having($having);
        }
		
        $this->db->query($select->__toString());
        $data = $this->db->fetch_all();
        
        if ((is_scalar($fields) && $fields != '*') || (is_array($fields) && count($fields) == 1)) {
            if (is_scalar($fields)) {
                $data = arrays::list_fields($data, $fields);
            } else {
                $key = arrays::first(array_keys($fields));
                $data = arrays::list_fields($data, is_int($key) ? first($fields) : $key);
            }
        }
        
        if (isset($limit) && $limit == 1) {
            $data = arrays::first($data);
        }
        
        return $data;
    }

    public function count($field = '*', $condition = null)
    {
        $select = SQL::select('count(' . $field . ')', $this->name);
        if (isset($condition)) {
            $select->where($condition);
        }
        return $this->db->value($select->__toString());
    }

    public function list_fields()
    {
        if (empty($this->fields)) {
            $this->fields = $this->db->list_fields($this->name);
        }
        return $this->fields;
    }
}
?>