<?php
class Template extends Object
{
    private $variables = array();
    
    const CACHE_DIR = 'templates';
    const FILE_EXT = '.tmpl';
    
    protected $modes = array();
    protected $mode = '';
    
    public function __construct()
    {
        $this->mode = uniqid();
        $this->modes[] = $this->mode;
    }

    public function assign($name, $value)
    {
        $this->variables[$this->mode][$name] = $value;
    }

    public function __get($name)
    {
        $value = null;

        if (isset($this->variables[$this->mode][$name])) {
            $value = $this->variables[$this->mode][$name];
        } else {
            $value = parent::__get($name);
        }
        
        return $value;
    }

    public function __set($name, $value)
    {
        $this->assign($name, $value);
    }
    
    public function __call($name, $args)
    {
        $value = null;
        
        switch ($name) {
        case 'include':
            $value = call_user_func_array(array(&$this, 'include_template'), $args);
            break;
            
        case 'return':
            $value = call_user_func_array(array(&$this, 'return_template'), $args);
            break;
            
        default:
            $this->Error->handle(0, "method Template:: {$name} does not exists");
        }
        
        return $value;
    }

    public function interpret_source($source)
    {
        return $this->execute($this->compile($source, true));
    }
    
    private function execute($source)
    {
        $Template = &$this;
        extract($GLOBALS);
        foreach ($this->modes as $mode) {
            if (isset($this->variables[$mode]) && is_array($this->variables[$mode])) {
                extract($this->variables[$mode]);
            }
        }
        
        $eval_error = false;
        ob_start();
        if (eval('?>' . $source) === false) {
            $eval_error = true;
        }
        $executed = ob_get_clean();
        
        return !$eval_error ? $executed : false;
    }

    private function interpret($template)
    {
        $template = path($template, self::FILE_EXT);

        $Cache = new FileCache('cache.templates', $template);
        $source = $Cache->content;
        if ($source === false) {
            if (!file_exists($template)) {
                return 'Error: template ' . $template . ' does not exist' . '<pre>' . $this->Error->trace() . '</pre>';
            }
            $content = file_get_contents($template);
            
            $source = $this->compile($content);
            $Cache->content = $source;
        }
        $executed = $this->execute($source);
        
        if ($executed === false) {
            return 'Error: in template ' . $template . '<pre>' . $this->Error->trace() . '</pre>';            
        }
        
        return $executed;
    }

    private function replace_filters($matches)
    {
        $str = $matches[1];
        $filters = explode('|', $matches[2]);
        foreach ($filters as $n => $filter) {
            if ($n > 0) {
                $argpos = strpos($filter, '(');
                if ($argpos !== false) {
                    $str = substr($filter, 0, $argpos + 1) . $str . ', ' . substr($filter, $argpos + 1);
                } else {
                    $str = $filter . '(' . $str . ')';
                }
            }
        }
        return '<?php echo ' . $str . '?>';
    }

    private function replace_object_filters($matches)
    {
        $parts = explode('.', $matches[1]);
        $var = array_shift($parts);
        foreach ($parts as $part) {
            $var .= '->' . $part;
        }

        $str = $var;
        $filters = explode('|', $matches[3]);
        foreach ($filters as $n => $filter) {
            if ($n > 0) {
                $argpos = strpos($filter, '(');
                if ($argpos !== false) {
                    $str = substr($filter, 0, $argpos + 1) . $str . ', ' . substr($filter, $argpos + 1);
                } else {
                    $str = $filter . '(' . $str . ')';
                }
            }
        }
        return '<?php echo ' . $str . '?>';
    }

    private function replace_variable($matches)
    {
        return "<?php echo (isset({$matches[1]}) ? {$matches[1]} : '')?>\n";
    }

    private function replace_object($matches)
    {
        $parts = explode('.', $matches[1]);
        $var = array_shift($parts);
        foreach ($parts as $part) {
            $var .= '->' . $part;
        }
        return "<?php echo (isset($$var) ? $$var : '$var')?>\n";
    }
            
    private function compile($source, $allow_php = false)
    {
        $w = '\pL\w';
        $var  = '\$[a-z_][a-z_0-9]*(?:\[[^\[\]]+\])*'; //@todo
        $var2 = '\$([a-z_][a-z_0-9]*(?:\[[^\[\]]+\])*)'; //@todo
        $var3 = '\$([a-z_][a-z_0-9]*(?:\.[a-z_0-9]+)*)';
        $arg = '(?:\d+|"[^"]+"|' . $var . ')';
        $arglist = "(?:$arg(?:\s*,\s*$arg)?)";
        $filter = "[a-zA-Z_][a-zA-Z_0-9]*(?:::[a-zA-Z_][a-zA-Z_0-9]*)?(?:\($arglist\))?"; //@todo
//        $condition = '.*(?![a-z_0-9]*\(.*\)).*';
        $condition = '[^\}]+';

        if (!$allow_php) {
            $source = preg_replace('/(<\?|<%|<script)/', "<?php echo '$1'?>", $source);
        }

        $source = preg_replace("/\{\*.*\*\}/s", '', $source);
        $source = preg_replace("/\{foreach\s+($var\s*as(?:\s*$var\s*=>)?\s*$var)\s*\}/i", '<?php foreach ($1) {?>', $source);
        $source = preg_replace("/\{foreach\s+\(($var\s*as(?:\s*$var\s*=>)?\s*$var)\)\s*\}/i", '<?php foreach ($1) {?>', $source);
        $source = preg_replace("/\{for\s+([^\\}]+)\s*\}/i", '<?php for ($1) {?>', $source);
        $source = preg_replace("/\{if\s+($condition)\s*\}/i", '<?php if ($1) {?>', $source);
        $source = preg_replace("/\{elseif\s+($condition)\s*\}/i", '<?php } elseif ($1) {?>', $source);
        $source = preg_replace("/\{else\}/i", '<?php } else {?>', $source);
        $source = preg_replace("/\{end\}/i", '<?php }?>', $source);

        $source = preg_replace("/\{~([^\}]+)\}/i",   '<?php echo ($Conf->LNG->has(\'$1\') ? $Conf->LNG->{"$1"} : \'$1\')?>', $source);
        $source = preg_replace("/\{@([$w\s]+)\}/i",  '<?php echo ($Conf->URL->has(\'$1\') ? $Conf->URL->{"$1"} : \'$1\')?>', $source);
        $source = preg_replace("/\{#([$w\s]+)\}/i",  '<?php echo ($Conf->ENV->has(\'$1\') ? $Conf->ENV->{"$1"} : \'$1\')?>', $source);
        $source = preg_replace("/\{\?([$w\s]+)\}/i", '<?php echo (isset($_REQUEST[\'$1\']) ? $_REQUEST[\'$1\'] : \'\')?>', $source);
        
        $source = preg_replace_callback("/\{($var)((?:\\|(?:$filter))+)\}/i", array(&$this, 'replace_filters'), $source);
        $source = preg_replace_callback("/\{($var2)\}/i", array(&$this, 'replace_variable'), $source);
        $source = preg_replace_callback("/\{$var3\}/i", array(&$this, 'replace_object'), $source);
        $source = preg_replace_callback("/\{($var3)((?:\\|(?:$filter))+)\}/i", array(&$this, 'replace_object_filters'), $source);
        
        $tag_name = '[a-z_-]+';
        $_tag_arg  = '[a-z_-]+\s*=\s*(?:' . $var . '|(?:\'[^\']*\')|(?:"[^"]*")|(?:`[^`]*`))';
        $tag_arg  = '([a-z_-]+)(?# 1)\s*=\s*(?:(' . $var . ')(?# 2)|(?:\'([^\']*)(?# 3)\')|(?:"([^"]*)(?# 4)")|(?:`([^`]*)(?# 5)`))';

        $tags = array();
        if (preg_match_all("/(?:<!--)?\{($tag_name)(?# 1)((?::$tag_name)?)(?# 2)((?:\s*$_tag_arg)*)(?# 3)\}(?:-->)?(.*?)(?# 4)(?:<!--)?\{\/\\1(?::\\2)?\}(?:-->)?/ims", $source, $tags, PREG_SET_ORDER)) {
            foreach ($tags as $tag) {
                $name      = $tag[1];
                $alias     = str_replace(':', '_', $tag[2]);
                $arguments = $tag[3];
                $content   = str_replace('$', '\$', $tag[4]);
                
                $args = array();
                preg_match_all("/$tag_arg/ims", $arguments, $args, PREG_SET_ORDER);
                $parameters = array();
                foreach ($args as $arg) {
                    $i = 2;
                    while (isset($arg[$i]) && !$arg[$i]) {$i ++;};
                    $value = $arg[$i];
                    if (preg_match("/$var/i", $value)) {
                        $parameters[] = "'{$arg[1]}' => $value\n";
                    } else {
                        ob_start();
                        eval('?>' . $value);
                        $value = ob_get_clean();
                        $delim = md5($value);
                        $parameters[] = "'{$arg[1]}' => <<<d{$delim}\n{$value}\nd{$delim}\n";
                    }
                }

                $label = str_replace('-', '_', $name);
                $replace = '<?php $Template->tag("' . $name . '", array(' . implode(',', $parameters) . '), ' . "<<<{$label}{$alias}\n{$content}\n{$label}{$alias}\n);?>";
                $source = str_replace($tag[0], $replace, $source);
            }
        }

        if (preg_match_all("/\{($tag_name)(?# 1)((?:\s*$_tag_arg)*)(?# 3)\s*\/\s*\}/ims", $source, $tags, PREG_SET_ORDER)) {
            foreach ($tags as $tag) {
                $name      = $tag[1];
                $arguments = $tag[2];
                preg_match_all("/$tag_arg/ims", $arguments, $args, PREG_SET_ORDER);
                $parameters = array();
                foreach ($args as $arg) {
                    $i = 2;
                    while (isset($arg[$i]) && !$arg[$i]) {$i ++;};
                    $value = $arg[$i];
                    if (preg_match("/$var/i", $value)) {
                        $parameters[] = "'{$arg[1]}' => $value\n";
                    } else {
                        ob_start();
                        eval('?>' . $value);
                        $value = ob_get_clean();
                        $delim = md5($value);
                        $parameters[] = "'{$arg[1]}' => <<<d{$delim}\n{$value}\nd{$delim}\n";
                    }
                }

                $replace = '<?php $Template->tag("' . $name . '", array(' . implode(',', $parameters) . '));?>';
                $source = str_replace($tag[0], $replace, $source);
            }
        }

/*        if (preg_match_all("/\{\/($tag_name)((?::$tag_name)?)\}/", $source, $tags, PREG_SET_ORDER)) {
            foreach ($tags as $tag) {
                $name = $tag[1];
                $alias = str_replace(':', '_', $tag[2]);
                $replace = "\n{$name}{$alias}\n);?>";
                $source = str_replace($tag[0], $replace, $source);
            }
        }
*/
        return $source;
    }

    public function tag($name, $arguments, $content = null)
    {
        $this->mode = uniqid();
        $this->modes[] = $this->mode;
        $this->variables[$this->mode] = array();
        $path = $this->PTH->tags . $name . '/tag.php';
        if (file_exists($path)) {
            if ($class_name = Framework::import_class($path)) {
                $prev_tag = $this->PTH->get('tag');
                $this->PTH->tag = $this->PTH->tags . $name . '/';
                $tag = new $class_name;
                $arguments['content'] = $content;
                $this->assign('params', $arguments);
                $content = $tag->handle($arguments);
                $this->PTH->tag = $prev_tag;
            } else {
                $content = 'Error: tag class "' . $name . '" is missing';
            }
        } else {
            $content = 'Error: tag file "' . $name . '" is missing';
        }
        echo $content;
        unset($this->variables[$this->mode]);
        array_pop($this->modes);
        $this->mode = end($this->modes);
    }
    
    public function include_template($template, $name = null)
    {
        $interpreted = $this->interpret($template);
        $template = path($template, self::FILE_EXT);
        if (!isset($name)) {
            $name = file::get_name($template);
        }
        $this->variables[$this->mode][$name] = $interpreted;
    }

    public function output($template)
    {
        echo $this->interpret($template);
    }

    public function return_template($template)
    {
        return $this->interpret($template);
    }
}
?>