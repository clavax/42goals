<?php
abstract class SQL_Query
{
    abstract public function build();
    
    public function __toString()
    {
        return $this->build();
    }
}
?>