<?php
abstract class BaseRoute extends Object
{
    protected $env = array();
    protected $included = array();
    const DEFAULT_ACTION = 'default';
    
    abstract protected function getController($query);
    
    public function follow($query)
    {
        $path = false;
        if (($query = $this->retriveLanguage($query)) !== false) {
            $path = $this->getController($query);
            if ($path) {
                $this->setEnviroment();
            }
        }
        return $path;
    }
    
    protected function retriveLanguage($query)
    {
        $chain = explode('/', $query);
        if (strlen($query) && count($chain) && !$this->CNF->site->single_lang) {
            if ($this->L10n->langExists($chain[0])) {
                $this->env['language'] = $chain[0];
                $this->env['language_id'] = $this->L10n->getLangId($chain[0]);
                $query = implode('/', array_slice($chain, 1));
            } else {
                $query = false;
            }
        } else {
            $language = false;
            /*if (Access::loggedIn()) {
                Access::setEnv(true);
                $Users = new UsersModel;
                $language = $Users->view($this->ENV->UID, 'language');
                if (!$this->L10n->langExists($language)) {
                    $language = false;
                }
            }*/ // @todo uncomment here for user language
            
            if (!$language) {
                $language = $this->L10n->getLangByDomain(array_get($_SERVER, 'HTTP_HOST'));
            }
            
            if (!$language) {
                $language = $this->CNF->default->language;
            }
            
            $this->env['language'] = $language;
            $this->env['language_id'] = $this->L10n->getLangId($language);
        }
        
        $this->ENV->language    = $this->env['language']; 
        $this->ENV->language_id = $this->env['language_id'];
        $this->ENV->host        = $this->CNF->languages[$this->env['language']]->domain;
        
        return $query;
    }
    
    protected function tryPath($path, $params)
    {
        if (($controller = $this->includeController($path)) !== false) {
            if ($controller instanceof BaseController && $env = $controller->checkParameters($params)) {
                $this->env = $env;
                $this->env['controller'] = $this->getControllerPath($path);
                $this->env['controller_name'] = file::last_dir(dirname($this->env['controller']));
                return $controller;
            }
        }
                
        return false;
    }
    
    protected function getControllerPath($query)
    {
        $this->env['query'] = $query; // should be removed?
        return arrays::first(glob($this->controllers_dir . $query . '/*.php', GLOB_NOSORT));
    }
    
    protected function includeController($path)
    {
        if (isset($this->included[$path])) {
            return $this->included[$path];
        }
        if ($controller_path = $this->getControllerPath($path)) {
            $declared_classes = get_declared_classes();
            include_once($controller_path);
            $classes = array_diff(get_declared_classes(), $declared_classes);
            foreach ($classes as $class) {
                if (is_subclass_of($class, 'BaseController')) {
                    $controller_name = $class;
                }
            }
            if ($controller_name) {
                $object = new $controller_name;
                $this->included[$path] = $object;
                return $object;
            }
        }
        return false;
    }
    
    protected function setEnviroment()
    {
        $this->ENV->add($this->env);
    }
    
}
?>