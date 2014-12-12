<?php
class ArrayRecursiveObject implements Iterator, ArrayAccess
{
    private $keys;
    private $keys_index;
    
    public function __construct($array = array())
    {
    	$this->keys = array();
        foreach ($array as $key => $value) {
            $this->__set($key, $value);
        }
    }

    public function __set($name, $value)
    {
    	if (is_array($value) && (!count($value) || !self::keys_int($value))) {
    		$value = new ArrayRecursiveObject($value);
    	}
    	$this->$name = $value;
    	if (!isset($this->keys_index[$name])) {
	    	$this->keys[] = $name;
	    	$this->keys_index[$name] = true;
    	}
    }
    
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->$name;
        } else {
            return null;
        }
    }
    
    public function has($name)
    {
    	return isset($this->$name);
    	//return in_array($name, $this->keys);
    }
    
    public function get($name, $default = null)
    {
        if ($this->has($name)) {
        	return $this->$name;
        } else {
            return $default;
        }
    }
    
    public function add($array)
    {
        foreach ($array as $key => $value) {
        	$this->__set($key, $value);
        }
    }
    
    public function keys()
    {
        return $this->keys;
    }
    
    private static function keys_int($array)
    {
        foreach ($array as $key => $value) {
            if (!is_int($key)) {
                return false;
            }
        }
        return true;
    }

    // for iterator
    public function rewind() {
        reset($this->keys);
    }

    public function current() {
        return $this->get(current($this->keys));
    }

    public function key() {
        return current($this->keys);
    }

    public function next() {
        return $this->get(next($this->keys));
    }

    public function valid() {
        return $this->key() !== false;
    }
    
    // for array access
    public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }
    
    public function offsetExists($offset) {
        return $this->has($offset);
    }
    
    public function offsetUnset($offset) {
        unset($this->$offset);
		$key = array_search($offset, $this->keys);
		if (isset($this->keys[$key])) {
            unset($this->keys_index[$this->keys[$key]]);
    		unset($this->keys[$key]);
		}
    }
    
    public function offsetGet($offset) {
        return $this->get($offset);
    }
}
?>