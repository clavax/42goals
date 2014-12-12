<?php
class Database_Postgre extends Database
{
    private
            $dsn,  // database connection string
            $host, // database host
            $port, // database port
            $user, // database user
            $pass, // database password
            $name; // database name
                
    public function connect(ArrayRecursiveObject $config)
    {
        $this->host = $config->get('host', 'localhost');
        $this->port = $config->get('port', 5432);
        $this->user = $config->get('user', '');
        $this->pass = $config->get('user', '');
        $this->name = $config->get('name');
        
        $this->build_dsn();
        
        if ($config->get('persistant') == true) {
            $this->link = pg_pconnect($this->dsn);
        } else {
            $this->link = pg_connect($this->dsn);
        }
        
        if (!is_resource($this->link)) {
            $error = $this->last_error();
            $this->error($error);
            return false;
        }

        return true;
    }
    
    private function build_dsn()
    {
        $this->dsn = 'host=' . $this->host
                   . ' port=' . $this->port
                   . ' dbname=' . $this->name;
                   
        if (strlen($this->user)) {
            $this->dsn .= ' user=' . $this->user;
        }
        if (strlen($this->pass)) {
            $this->dsn .= ' password=' . $this->pass;
        }
    }

    public function execute($query)
    {
        return pg_query($this->link, $query);
    }
    
    public function last_errno()
    {
        return 0; // @todo: errno not implemented in Postgre?
    }

    public function last_error()
    {
        return is_resource($this->link) ? pg_last_error($this->link) : false;
    }
    
    public function seek($result, $row)
    {
        return pg_result_seek($result, $row);
    }
    
    public function num_rows()
    {
        return pg_num_rows(end($this->result));
    }

    public function fetch_array($result)
    {
        return pg_fetch_array($result);  
    }

    public function fetch_assoc($result)
    {
        return pg_fetch_assoc($result);
    }
    
    public function affected_rows()
    {
        return pg_affected_rows($this->result);
    }
    
    public function insert_id()
    {
        return $this->value('select lastval()');
    }

    public function list_fields($table)
    {
        $fields = pg_meta_data($this->link, $table);
        return array_keys($fields);
    }        
    
    private function set_character_set($encoding)
    {
        return pg_set_client_encoding($this->link, $encoding);
    }
}
?>