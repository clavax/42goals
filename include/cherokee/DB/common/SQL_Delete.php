<?php
import('cherokee.DB.common.SQL_Query');

abstract class Common_SQL_Delete extends SQL_Query
{
    protected $table;
    protected $condition;

    public function __construct($table, $condition = null)
    {
        $this->table = $table;
        $this->condition = $condition;
    }

    public function build()
    {
        $query = SQL::quote("delete from %", $this->table);

        if (isset($this->condition)) {
            $query .= ' where ' . $this->condition;
        }

        return $query;
    }
}
?>