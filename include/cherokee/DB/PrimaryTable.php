<?php
import('cherokee.DB.DataTable');

class PrimaryTable extends DataTable
{
    protected $pk;

    public function __construct($name, $pk)
    {
        parent::__construct($name);
        $this->pk = $pk;
    }

    public function __get($name)
    {
        if ($name == 'pk') {
            return $this->pk;
        } else {
            return parent::__get($name);
        }
    }

    public function update($id, $data)
    {
        if ($this->exists($id)) {
            return parent::update_where($data, SQL::quote('% = ?', $this->pk, $id));
        } else {
            $data[$this->pk] = $id;
            return parent::insert($data);
        }
    }

    public function delete($id)
    {
        return parent::delete_where(SQL::quote('% = ?', $this->pk, $id));
    }
    
    public function view($id, $fields = '*')
    {
        return $this->select($fields, SQL::quote('% = ?', $this->pk, $id), null, 1);
    }

    public function exists($id)
    {
        return parent::exists_where(SQL::quote('% = ?', $this->pk, $id));
    }
}
?>