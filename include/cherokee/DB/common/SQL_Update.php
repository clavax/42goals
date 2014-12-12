<?php
import('cherokee.DB.common.SQL_Query');

abstract class Common_SQL_Update extends SQL_Query
{
    protected $data;
    protected $table;
    protected $condition;

    public function __construct($table, array $data, $condition = null)
    {
        $this->table = $table;
        $this->data = $data;
        $this->condition = $condition;
    }

    public function build()
    {
        $sets = null;
        foreach ($this->data as $field => $value) {
            if (isset($sets)) {
                $sets .= SQL::quote(', % = ?', $field, $value);
            } else {
                $sets = SQL::quote('% = ?', $field, $value);
            }
        }

        $query = SQL::quote('update %', $this->table) . ' set ' . $sets;

        if (isset($this->condition)) {
            $query .= ' where ' . $this->condition;
        }
        
        return $query;
    }
}
?>