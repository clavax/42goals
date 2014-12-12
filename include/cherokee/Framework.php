<?php
class Framework
{
    private static $loaded;
    private static $objects;
    private static $registry;
    private static $instance;
    private static $imported_classes;

    private function __construct()
    {
        // empty so far ...
    }
    
    public function load()
    {
        if (self::$loaded) {
            return false;
        }
        self::$loaded = true;
        
        //define URLs and Paths
        foreach (array('ORIG_PATH_TRANSLATED', 'PATH_TRANSLATED', 'SCRIPT_FILENAME') as $candidate) {
            if (isset($_SERVER[$candidate]) && !empty($_SERVER[$candidate])) {
                $GLOBALS['PTH']['main'] = dirname(str_replace('\\', '/', str_replace('\\\\', '\\', $_SERVER[$candidate]))) . '/';
            }
        }

        $GLOBALS['URL']['main'] = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
        if ($GLOBALS['URL']['main'] != '/') {
            $GLOBALS['URL']['main'] .= '/';
        }
        $protocol = 'http://'; // @todo
        $GLOBALS['URL']['host'] = $protocol . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');

        // load files
        self::import('common');
        self::import('ArrayRecursiveObject');
        self::import('Conf');
        self::import('Memory');
        
        $M = new Memory;
        self::set('Mem', $M);
        $M->reg('loaded');
        
        // config handler
        $Conf = Conf::instance();
        $Conf->PTH = $GLOBALS['PTH'];
        $Conf->URL = $GLOBALS['URL'];
        $Conf->ENV = array();
        $Conf->CNF = array();
        $Conf->LNG = array();
        
        self::set('Memcache', new Memcache);
        $this->Memcache->connect('localhost');
                
        self::set('Conf', $Conf);

        $Conf->load('main.path');
        $Conf->load('config.cherokee', 'CNF');
        $this->Mem->reg('load config');
        
        // load kernel and libraries
        self::import('cherokee.Object');
        $this->Mem->reg('included Object');
        
        self::import('cherokee.*');
        self::import('cherokee.DB.*');
        self::import('cherokee.DB.' . $Conf->CNF->database->type . '.*');
        self::import('cherokee.Routes.*');
        self::import('cherokee.Cache.FileCache');
        self::import('lib.array');
        self::import('lib.locale');
        self::import('lib.json');
        
        self::import('cherokee.Access');
        $this->Mem->reg('included Access');
        
        // error handler
        $Error = new Error($Conf->PTH->logs . 'error.log', $Conf->CNF->kernel->log_format, Error::LINE_END_WIN);
        self::set('Error', $Error);                                         // error handler             
        self::set('Timer', new Timer);                                      // timer
        self::set('T', new Template);                                       // template engine
        $L10n = new L10n($Conf->PTH->config . 'languages.ini');
        self::set('L10n', $L10n);  // localization engine
        
        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set($Conf->CNF->kernel->timezone);
        }
        setlocale(LC_ALL, 'en_US');
        $this->Timer->reg('Loaded framework');
        $this->Mem->reg('loaded framework');
    }

    private function __clone()
    {
        // to prevent cloning object
    }

    public static final function instance()
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }
        
        return self::$instance;
    }

    public static function has($name)
    {
        return isset(self::$objects[$name]);
    }
    
    public static function set($name, &$object)
    {
        self::$objects[$name] = &$object;
    }
    
    public static function register($name, $method, $params = array())
    {
        self::$registry[$name] = array($method, $params);
    }

    public static function get($name)
    {
        $object = false;
        if (isset(self::$objects[$name])) {
            // check saved objects
            $object = self::$objects[$name];
        } else {
            // check registry
            if (isset(self::$registry[$name])) {
                list($method, $params) = self::$registry[$name];
                if (!is_array($params)) {
                    $params = array($params);
                }
                
                // check method format:
                $paamayim_nekudotayim = substr_count($method, '::');
                switch ($paamayim_nekudotayim) {
                case 0:
                    // method given as a class name
                    // @todo: call constructor
                    break;
                    
                case 1:
                    // method given as class_name::method_name
                    list($class_name, $method_name) = explode('::', $method, 2);
                    $object = call_user_func_array(array($class_name, $method_name), $params);
                    break;
                    
                default:
                    // method given with namespace information, available from php 5.3.0
                }
                
                if (is_object($object)) {
                    self::set($name, $object);
                }
            }
        }
        
        return $object;
    }

    public function __get($name)
    {
        if (($object = self::get_object($name)) !== false) {
            return $object;
        } else {
            throw new Exception('Unknown object');
        }
    }

    public static function import($path)
    {
        $parts = explode('.', $path);
        if (count($parts) > 1) {
            $dir = self::get('Conf')->PTH->{$parts[0]};
            for ($i = 1; $i < count($parts) - 1; $i ++) {
                $dir .= $parts[$i] . '/';
            }
        } else {
            $i = 0;
            $dir = '';
        }
        if ($parts[$i] == '*') {
            $files = glob($dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    include_once($file);
                }
            }
        } else {
            $included = include_once($dir . $parts[$i] . '.php');
            if (!$included) {
                debug_print_backtrace();
            }
        }
    }
    
    public static function import_class($path)
    {
        if (isset(self::$imported_classes[$path])) {
            return self::$imported_classes[$path];
        } else {
            $declared_classes = get_declared_classes();
            require_once($path); // TODO: implement through import();
            if ($class_name = arrays::first(array_reverse(array_diff(get_declared_classes(), $declared_classes)))) {
                self::$imported_classes[$path] = $class_name;
                return $class_name;
            } else {
                return false;
            }
        }
    }
    
    public static function transform_path($path, $ext = '')
    {
        $parts = explode('.', $path);
        if (count($parts) > 1) {
            $Conf = self::get('Conf');
            if (!$Conf->PTH->has($parts[0])) {
                return false;
            }
            $dir = $Conf->PTH->{$parts[0]};
            for ($i = 1; $i < count($parts) - 1; $i ++) {
                $dir .= $parts[$i] . '/';
            }
        } else {
            $i = 0;
            $dir = '';
        }
        $filename = $parts[$i];

//        echo preg_match('/~/', $filename), ' ', $filename, "\n";
        
        if (preg_match('/~/', $filename)) {
            $filename = preg_replace('/~/', '.', $filename, 1);
        } else {
            $filename .= $ext;
        }
        
        return $dir . $filename;
    }

    public static function addEnviroment($content)
    {
        $search = array(
//            '{!timer}',
            '{!gen}',
//            '{!database}',
//            '{!queries}',
//            '{!env}',
//            '{!conf}',
            '{!memory}',
            '{!memreport}',
        );
        $replace = array(
//            self::get('Timer')->report(),
            self::get('Timer')->getTime(),
//            self::has('db') ? self::get('db')->report(true) : 'No database used',
//            self::has('db') ? self::get('db')->getQueries() : '0',
//            print_r(self::get('Conf')->ENV, true),
//            print_r(self::get('Conf')->CNF, true),
            self::get('Mem')->format(self::get('Mem')->getUsage()),
            str_replace("\n", '\n', self::get('Mem')->report())
        );
        return str_replace($search, $replace, $content);
    }

    // deprecated methods:
    public static function register_object($name, &$object)
    {
        self::set($name, $object);
    }

    public static function get_object($name)
    {
        return self::get($name);
    }
}
?>
