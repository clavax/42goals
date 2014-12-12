<?php
import('base.controller.BaseTag');

class LanguageTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->T->query = preg_replace('/^' . preg_quote($this->URL->home, '/') . '/', '', $this->URL->self);
        $this->T->languages = $this->CNF->languages;
        return $this->T->return_template('tag.language');
    }
}
?>