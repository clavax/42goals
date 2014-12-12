<?php
import('cherokee.DB.common.SQL_Query');

abstract class Common_SQL_Insert extends SQL_Query
{
    protected $data;
    protected $table;

    public function __construct($data, $table)
    {
        $this->data = $data;
        $this->table = $table;
    }

    public function build()
    {
        $query = 'insert into % (%) values (?)';
        return SQL::quote($query, $this->table, array_keys($this->data), array_values($this->data));
    }
}
?>