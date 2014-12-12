<?php
class L10n extends Object
{
    private $languages;
    
    public function __construct($path)
    {
        $this->loadLanguages($path);
    }
    
    public function loadLanguages($path)
    {
        $data = $this->Conf->parse($path);
        foreach ($data as $name => $language) {
            $this->languages[$name] = $language;
        }
        
        $this->CNF->languages = $data;
    }
    
    public function langExists($name)
    {
        return isset($this->languages[$name]);
    }
    
    public function getLangId($name)
    {
        $id = false;
        if ($this->langExists($name)) {
            $id = $this->languages[$name]['code'];
        }
        return $id;
    }
    
    public function getLangByDomain($domain)
    {
        $lang = false;
        foreach ($this->languages as $name => $language) {
            if ($language['domain'] == $domain) {
                $lang = $name;
                break;
            }
        }
        return $lang;
    }
    
    public function getLangName($id)
    {
        return 'not implemented';
    }
}
?>