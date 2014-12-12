<?php
abstract class BaseRouter extends Object
{
    protected static $instance;
    protected $routes = array();
    protected $status;
    protected $ActiveRoute;
    protected $ErrorRoute;
    protected $error_handers;
    protected $error_messages;
    protected $query;
    
    const STATUS_OK          = 200;
    const STATUS_NOT_FOUND   = 404;
    const STATUS_FORBIDDEN   = 403;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_APP_ERROR   = 500; 
    
    public function __construct()
    {
        $this->error_handlers = array(
            self::STATUS_NOT_FOUND    => array(&$this, 'notFound'),
            self::STATUS_FORBIDDEN    => array(&$this, 'forbidden'),
            self::STATUS_BAD_REQUEST  => array(&$this, 'badRequest'),
            self::STATUS_APP_ERROR    => array(&$this, 'appError'),
        );
        $this->error_messages = array(
            self::STATUS_NOT_FOUND    => 'Not found',
            self::STATUS_FORBIDDEN    => 'Forbidden',
            self::STATUS_BAD_REQUEST  => 'Bad request',
            self::STATUS_APP_ERROR    => 'Application error',
        );
    }
        
    public function setErrorRoute(&$route)
    {
        $this->ErrorRoute = $route;
    }
    
    public function addRoute($route)
    {
        array_push($this->routes, $route);
    }
    
    abstract protected function getQuery();
    
    abstract protected function getRequest();
    
    public function route()
    {
    	$this->getRequest();
        $this->query = $this->getQuery();
            	
        foreach ($this->routes as $route) {
            if ($controller = $route->follow($this->query)) {
                $this->ActiveRoute = $route;
                break;
            }
        }
        
        $content = false;
        $status = false;
        if ($controller) {
            $content = $this->callController($controller);
            $status = $controller->getStatus();
        }
        if ($content === false) {
            $this->status = $status ? $status : self::STATUS_NOT_FOUND;
            $content = $this->errorController();
        }
        return $content;
    }
    
    abstract protected function callController($controller);
        
    protected function errorController()
    {
        $content = false;
        if ($this->ErrorRoute instanceof BaseRoute) {
	        if ($controller = $this->ErrorRoute->follow($this->status)) {
	            $this->ActiveRoute = $this->ErrorRoute;
	            if (($content = $this->callController($controller)) === false) {
	                $content = $this->error();
	            }
            }
        } else {
        	$content = $this->error();
        }
        return $content;
    }
    
    protected function error()
    {
        $content = false;
        if (isset($this->error_handlers[$this->status])) {
            $content = call_user_func($this->error_handlers[$this->status]);
        } else {
            $content = $this->appError();
        }
        return $content;
    }
    
    protected function notFound()
    {
        // 404
        header('HTTP/1.1 ' . $this->error_messages[self::STATUS_NOT_FOUND]);
        header('Status: 404 ' . $this->error_messages[self::STATUS_NOT_FOUND]);
        return '404 ' . $this->error_messages[self::STATUS_NOT_FOUND] . "\n";
    }
    
    protected function forbidden()
    {
        // 404
        header('HTTP/1.1 ' . $this->error_messages[self::STATUS_NOT_FOUND]);
        header('Status: 403 ' . $this->error_messages[self::STATUS_FORBIDDEN]);
        return '403 ' . $this->error_messages[self::STATUS_FORBIDDEN] . "\n";
    }
    
    protected function badRequest()
    {
        // 400
        header('HTTP/1.1 400 ' . $this->error_messages[self::STATUS_BAD_REQUEST]);
        header('Status: 400 ' . $this->error_messages[self::STATUS_BAD_REQUEST]);
        return '404 ' . $this->error_messages[self::STATUS_BAD_REQUEST] . "\n";
    }
    
    protected function appError()
    {
        // 500
        header('HTTP/1.1 500 ' . $this->error_messages[self::STATUS_APP_ERROR]);
        header('Status: 500 ' . $this->error_messages[self::STATUS_APP_ERROR]);
        return '500 ' . $this->error_messages[self::STATUS_APP_ERROR] . "\n";
    }
}
?>