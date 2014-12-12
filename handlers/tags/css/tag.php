<?php
import('base.controller.BasePackedTag');

class CssTag extends BasePackedTag
{
    protected $url = 'css';
    protected $ext = '.css';
    
    public function __construct()
    {
        $this->cache_dir = $this->PTH->css . 'cache/';
        $this->template = 'tag.css';
    }
    
    public function handle(array $params = array())
    {
        switch (array_get($params, 'type')) {
        case 'mobile':
            return $this->handleMobile($params);
            break;
            
        default:
            return $this->handleDefault($params);
        }
    }

    public function handleDefault(array $params = array())
    {
        $files = array(
            'public.css.common.main',
        );
        
        return $this->handleFiles('packed', $files, array_get($params, 'packed'), array_get($params, 'flush'));
    }
    
    public function handleMobile(array $params = array())
    {
        $files = array(
            'public.css.common.mobile',
        );
        
        return $this->handleFiles('mobile', $files, array_get($params, 'packed'), array_get($params, 'flush'));
    }
    
    protected function pack($filename, array &$packed_files)
    {
        import('3dparty.cssmin');
        
        $filepath = path($filename, '.css');
        $dir = dirname(realpath($filepath)) . '/';
        
        if (in_array($filepath, $packed_files)) {
            return false;
        } else {
            $packed_files[] = $filepath;
        }
                
        $content = file::get_contents($filepath);
        if (preg_match_all('/@import\s+(\'|")([^\'"]+)(?1);?/m', $content, $matches, PREG_SET_ORDER) !== false) {
            foreach ($matches as $match) {
                $packed  = $this->pack(realpath($dir . $match[2]), $packed_files);
                $content = str_replace($match[0], $packed, $content);
            }
        }
        return cssmin::minify($content);
    }    
}
?>