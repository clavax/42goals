<?php
import('base.controller.BasePackedTag');
import('lib.json');

class JsTag extends BasePackedTag
{
    protected $url = 'js';
    protected $ext = '.js';
    
    public function __construct()
    {
        $this->cache_dir = $this->PTH->js . 'cache/';
        $this->template = 'tag.js';
    }
    
    public function handle(array $params = array())
    {
        switch (array_get($params, 'type')) {
        case 'mobile':
            return $this->handleMobile($params);
            break;
            
        case 'admin':
            return $this->handleAdmin($params);
            break;
            
        default:
            return $this->handleDefault($params);
        }
    }
    
    public function handleDefault(array $params = array())
    {
        $files = array(
            'mohawk.kernel.Mohawk',
            'js.page.goals',
            'js.page.report',
            'js.page.registration',
            'js.page.settings',
            'js.page.home',
            'js.page.demo',
            'js.page.apps',
            'js.page.login',
            'js.page.archive',
            'js.page.posts',
            'js.page.me',
            'js.page.me-compares',
            'js.page.user',
            'js.page.communities',
        );
        $this->T->getsession = array_get($params, 'getsession', 'no');
        return $this->handleFiles('packed', $files, $params['packed'], array_get($params, 'flush'));
    }
    
    public function handleAdmin(array $params = array())
    {
        $files = array(
            'mohawk.kernel.Mohawk',
            'js.page.templates-admin',
            'js.page.icons',
            'js.page.communities-admin',
        );
        $this->T->getsession = array_get($params, 'getsession', 'no');
        return $this->handleFiles('admin', $files, $params['packed'], array_get($params, 'flush'));
    }
    
    public function handleMobile(array $params = array())
    {
        $files = array(
            'mohawk.kernel.Mohawk',
            'js.page.login',
            'js.page.mobile',
        );
        $this->T->getsession = array_get($params, 'getsession', 'no');
        return $this->handleFiles('mobile', $files, array_get($params, 'packed'), array_get($params, 'flush'));
    }
    
    public function handleFiles($name, array $files, $packed = 'no', $flush = false) 
    {
        $this->T->url = $this->Conf->URL;
        $this->T->url['sso'] = $this->CNF->site->sso;
        $this->T->env  = $this->Conf->ENV;
        unset($this->T->env['controller'], $this->T->env['controller_name']);
        
        $this->T->sso = $this->CNF->site->sso_url;
        
        return parent::handleFiles($name, $files, $packed, $flush);
    }
        
    protected function packTemplate($filename, &$packed_files)
    {
        $filepath = $this->PTH->public . 'html/' . $filename . '.tmpl';
        
        if (in_array($filepath, $packed_files)) {
            return false;
        } else {
            $packed_files[] = $filepath;
        }

        $name = file::get_name($filepath);
//        $name = $filename;
        $name = preg_replace('/\W/', '_', $name);
        $name = str::toupper($name);
        
        $content = file_get_contents($filepath);
        $content = str_replace('"', '\"', $content);
        $content = preg_replace('/\r\n|\r|\n/', '" + "\n', $content);
        $content = 'window.' . $name . ' = "' . $content . '";';

        return $content;
    }
    
    protected function packLanguage($filename, &$packed_files)
    {
        $filepath = $this->PTH->public . 'lang/' . $this->ENV->language . '/' . $filename . '.ini';

        if (in_array($filepath, $packed_files)) {
            return false;
        } else {
            $packed_files[] = $filepath;
        }

        $lng = $this->Conf->parse($filepath);
        
        $content = 'Mohawk.Loader.extendLanguage(' . json::encode($lng) . ')';
        
        return $content;
    }
    
    protected function pack($filename, array &$packed_files)
    {
        import('3dparty.jsmin');
        
        $first = strtok($filename, '.');
        if (!$this->PTH->has($first)) {
            $filename = 'js.' . $filename;
        }
        $filepath = path($filename, '.js');
        
        if (in_array($filepath, $packed_files)) {
            return false;
        } else {
            $packed_files[] = $filepath;
        }
        
        $content = file::get_contents($filepath);
        if (preg_match_all('/include\((\'|")((?:\w+)(?:\.\w+)*)(?1)\);?/m', $content, $matches, PREG_SET_ORDER) !== false) {
            foreach ($matches as $match) {
                $packed = $this->pack($match[2], $packed_files);
                $content = str_replace($match[0], $packed, $content);
            }
        }

        if (preg_match_all('/(?:Mohawk\.)?Loader\.includeTemplate\((\'|")((?:[\w-]+)(?:\.[\w-]+)*)(?1)\);?/m', $content, $matches, PREG_SET_ORDER) !== false) {
            foreach ($matches as $match) {
                $packed = $this->packTemplate($match[2], $packed_files);
                $content = str_replace($match[0], $packed, $content);
            }
        }

        if (preg_match_all('/(?:Mohawk\.)?Loader\.includeLanguage\((\'|")((?:[\w-]+)(?:\.[\w-]+)*)(?1)\);?/m', $content, $matches, PREG_SET_ORDER) !== false) {
            foreach ($matches as $match) {
                $packed = $this->packLanguage($match[2], $packed_files);
                $content = str_replace($match[0], $packed, $content);
            }
        }
        
        return JSMin::minify($content);
//        return $content;
    }    
}
?>