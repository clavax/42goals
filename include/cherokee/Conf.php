<?php
//import('cherokee.ArrayRecursiveObject');

class Conf
{
    private static $instance;
    private $objects;
    private $loaded = array();
    private $variables = array();

    private function __construct()
    {
		$this->objects = new ArrayRecursiveObject();
    }

    private function __clone() {

    }

    public static final function instance()
    {
        if (!isset(self::$instance)) {
            $class_name = __CLASS__;
            self::$instance = new $class_name;
        }
        return self::$instance;
    }

    public function set($name, $value)
    {
        $keys = explode('/', $name);
        $var = &$this->objects;
        foreach ($keys as $key) {
            if (isset($var[$key])) {
                $var = &$var[$key];
            } else {
                return false;
            }
        }
        if ($var != $value) {
            $var = $value;
        }
    }

    public function __get($name)
    {
    	return $this->objects->get($name);
    }

    public function get($name, $default = null)
    {
    	return $this->objects->get($name, $default);
    }
        
    public function has($name)
    {
    	return $this->objects->has($name);
    }
    
    public function __set($name, $value)
    {
    	return $this->objects->__set($name, $value);
    }

    private function replace_var($m)
    {
    	extract($this->variables);
    	return eval("return isset({$m[1]}) ? {$m[1]} : null;");
    }
    
    private function evaluate($value, $as_lang = false, $variables = array())
    {
        if (is_scalar($value)) {
        	$this->variables = $variables;
        	$var = '\$[a-z_][a-z_0-9]*(?:\[(?:(?:\'[a-z_0-9]+\')|(?:"[a-z_0-9]+")|(?:[0-9]+))\])*';
            $value = preg_replace_callback("/\{($var)\}/i", array(&$this, 'replace_var'), $value);
            $this->variables = array();
        } else if (is_array($value)) {
            $evaluated = array();
            foreach ($value as $key => $elem) {
                $eval = self::evaluate($elem, $as_lang, $evaluated);
                if (is_scalar($elem) && $as_lang) {
                    $evaluated[str::ucwords($key)] = str::ucwords($eval);
                    $evaluated[str::ucfirst($key)] = str::ucfirst($eval);
                    $evaluated[str::toupper($key)] = str::toupper($eval);
                    $evaluated[str::tolower($key)] = str::tolower($eval);
                }
                $evaluated[$key] = $this->evaluate($elem, $as_lang, $evaluated);
            }
            $value = $evaluated;
        }
        return $value;
    }

    public function parse($filename, $as_lang = false)
    {
        $Memcache = Framework::get('Memcache');
        $parsed = $Memcache->get('lang_' . $filename);
        if (!$parsed) {
            $filename = path($filename, '.ini');
            $data = array(parse_ini_file($filename, true));
            foreach ($data as &$value) {
                $value = $this->evaluate($value, $as_lang);
            }
            $parsed = reset($data);
            $Memcache->set('lang_' . $filename, $parsed, MEMCACHE_COMPRESSED, 3600);
        }
        return $parsed;
    }
    
    public function load($filename, $section = '', $as_lang = false)
    {
        $filename = path($filename, '.ini');
        
        if (file_exists($filename)) {
            if ($data = $this->parse($filename, $as_lang)) {
                if ($section) {
                	if (!$this->objects->has($section)) {
                		$this->objects->$section = new ArrayRecursiveObject();
                	}
                    $globals = $this->objects->get($section);
                } else {
                    $globals = &$this->objects;
                }
            	foreach ($data as $key => $value) {
                    if ($globals->has($key)) {
                    	if ($globals->$key instanceof ArrayRecursiveObject) {
                    		$globals->$key->add($value);
                    	} else {
                    		$globals->$key = $value;
                    	}
                    } else {
                        $globals->$key = $value;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            $message = 'Config file ' . $filename . ' does not exists in ' . getcwd();
            if ($Error = Framework::get('Error')) {
                $Error->log($message);
            } else {
                describe(debug_backtrace(), 1);
                die($message);
            }
            return false;
        }
    }
    
    public function loadLanguage($file, $language = null)
    {
        if (!in_array($file, $this->loaded)) {
            $this->load('lang.' . ($language ? $language : $this->ENV->language) . '.' . $file, 'LNG', true);
            $this->loaded[] = $file;
        }
    }
    /*public function __destruct()
    {
        $this->save();
    }*/
}
?>