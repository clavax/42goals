<?php
import('lib.headers');
import('cherokee.Routers.BaseRouter');

class HttpRouter extends BaseRouter
{
    public function getRequest()
    {
        if (isset($_SERVER['HTTP_X_REQUEST_METHOD'])) {
            $_SERVER['REQUEST_METHOD'] = $_SERVER['HTTP_X_REQUEST_METHOD'];
        }
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $GLOBALS['_' . $method] = array();
        switch ($method) {
        case 'GET':
        case 'POST':
            break;
        case 'PUT':
        case 'DELETE':
        case 'HEAD':
        case 'OPTIONS':
            if (($put = fopen('php://input', 'rb')) !== false) {
                $query = '';
                while (!feof($put)) {
                    $query .= fgets($put);
                }
                fclose($put);
                $data = array();
                parse_str($query, $data);
                $_REQUEST += $data;
                $GLOBALS['_' . $method] += $data;
            }
            break;
        }
    }
    
    protected function getQuery()
    {
        $query = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $query = urldecode(preg_replace('#' . preg_quote($this->URL->main, '#') . '#', '', $_SERVER['REQUEST_URI'], 1));
            if (($pos = strpos($query, '?')) !== false) {
                $query = substr($query, 0, $pos);
            }
        } else {
            $query = get_user_var('query');
        }
        if (substr($query, -1, 1) == '/') {
            $query = substr($query, 0, - 1);
        }
        return $query;
    }
    
    protected function callController($controller)
    {
        Access::setEnv();
        
        $content = false;
        $method = ucfirst(strtolower(array_get($_SERVER, 'REQUEST_METHOD')));
        $action = ucfirst(strtolower($this->ENV->has('action') ? $this->ENV->action : BaseRoute::DEFAULT_ACTION));
        
        $names = array(
            'handle' . $method . $action,
            'handle' . $action,
            'handle' . $method,
            'handle'
        );
        $found = false;
        foreach ($names as $name) {
            if (method_exists($controller, $name)) {
            	$found = true;
                break;
            }
        }

        if ($found) {
	        $this->PTH->this = dirname($this->ENV->controller) . '/';
	        $this->URL->home = $this->URL->main . ($this->ENV->has('language') && !$this->CNF->site->single_lang ? $this->ENV->language . '/' : '');
	        $this->URL->this = $this->URL->home . $this->ENV->query . (strlen($this->ENV->query) ? '/' : '');
	        $this->URL->self = $this->URL->main . $this->query . (strlen($this->query) ? '/' : '');
	        $content = $controller->$name($_REQUEST);
        }

        return $content;
    }    
}
?>