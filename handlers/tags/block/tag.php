<?php
import('base.controller.BaseTag');

class BlockTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $type = ucfirst(strtolower($params['type']));
        $type = preg_replace_callback('/-([a-z])/', array(&$this, 'replaceDash'), $type);
        $method = 'handle' . $type;
        
        $this->T->content = $this->T->interpret_source($params['content']);
        $this->T->params = $params;
        
        if (method_exists($this, $method)) {
            return $this->$method($params);
        } else {
            if (file_exists($template = $this->PTH->tag . $params['type'] . '.tmpl')) {
                return $this->T->return($template);
            } else {
                return 'Error: type "' . $params['type'] . '" does not exist';
            }
        }
    }
    
    private function replaceDash($m)
    {
    	return strtoupper($m[1]);
    }
}
?>
