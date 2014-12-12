<?php
/**
 * Session handler
 *
 */
class Session
{
    private static $instance;
    private $id;
    
    private function __construct()
    {
        if (isset($_REQUEST['_PHPSESSID'])) {
            session_id($_REQUEST['_PHPSESSID']);
        }
        session_start();
        $this->id = session_id();
    }
    
    private function __clone()
    {
        // to prevent cloning object
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($sid)
    {
        return session_id($sid);
    }
    
    public function __get($name)
    {
        return array_get($_SESSION, $name);
    }
    
    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }
    
    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }
    
    public function __unset($name)
    {
        unset($_SESSION[$name]);
    }
    
    public function destroy()
    {
        return session_destroy();
    }
    
    public function restart($sid)
    {
        if ($this->getId() !== $sid) {
            $e = Framework::get('Error');
            $this->destroy();
            $this->setId($sid);
            session_start();
        }
    }
    
    public static final function instance()
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }
        
        return self::$instance;
    }
}
?>