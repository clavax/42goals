<?php
import('base.controller.BaseCommand');

class RefreshCacheCommand extends BaseCommand
{
    public function handle(array $request = array())
    {
        set_time_limit(300);
        foreach ($this->CNF->languages as $name => $language) {
            $this->ENV->language = $name;
            
            if (!$this->CNF->site->local) {
                import('tags.js.tag');
                $JsTag = new JsTag();
                $JsTag->handleDefault(array('packed' => true, 'flush' => true));
                $JsTag->handleMobile(array('packed' => true, 'flush' => true));
        
                import('tags.css.tag');
                $CssTag = new CssTag();
                $CssTag->handleDefault(array('packed' => true, 'flush' => true));
                $CssTag->handleMobile(array('packed' => true, 'flush' => true));
            }
            $this->Memcache->flush();
        }
        
        return 'Done';
    }
}
?>