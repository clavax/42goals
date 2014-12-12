<?php
class Database_Mysql extends Database
{
    private
            $host, // database host
            $port, // database port
            $user, // database user
            $pass, // database password
            $name; // database name
                
    public function connect(ArrayRecursiveObject $config)
    {
        $this->host = $config->get('host', 'localhost');
        $this->port = $config->get('port', 3306);
        $this->user = $config->get('user', 'root');
        $this->pass = $config->get('pass', '');
        $this->name = $config->get('name');
        
        if ($config->get('persistant') == true) {
            $this->link = mysql_pconnect($this->host . ':' . $this->port, $this->user, $this->pass);
        } else {
            $this->link = mysql_connect($this->host . ':' . $this->port, $this->user, $this->pass);
        }
        if (!is_resource($this->link)) {
            $this->error(mysql_error());
            return false;
        }

        if (!mysql_select_db($this->name, $this->link)) {
            $error = $this->last_error();
            $this->error($error);
            return false;
        }

        $info = mysql_get_server_info($this->link);
        if ($info >= '4.1' && $config->get('charset')) {
            $this->set_character_set($config->get('charset'));
        }

        return true;
    }

    public function get_link()
    {
        return $this->link;
    }
    
    public function execute($query)
    {
        return mysql_query($query, $this->link);
    }
    
    public function last_errno()
    {
        return mysql_errno($this->link);  
    }

    public function last_error()
    {
        return is_resource($this->link) ? mysql_error($this->link) : false;
    }
    
    public function seek($result, $row)
    {
        if (mysql_num_rows($result)) {
            return mysql_data_seek($result, $row);
        }
    }
    
    public function num_rows()
    {
        return mysql_num_rows(end($this->result));
    }

    public function fetch_array($result)
    {
        return mysql_fetch_array($result);  
    }

    public function fetch_assoc($result)
    {
        return mysql_fetch_assoc($result);  
    }
    
    public function affected_rows()
    {
        return mysql_affected_rows($this->link);
    }
    
    public function insert_id()
    {
        return mysql_insert_id($this->link);        
    }

    public function list_fields($table)
    {
        $this->query('show columns from %', $table);
        $fields = array();
        while ($field = $this->fetch('Field')) {
            $fields[] = $field;
        }
        return $fields;
    }        
    
    private function set_character_set($encoding)
    {
        $this->query("set character set $encoding");
        $this->query("set names $encoding");
    }
}
?>