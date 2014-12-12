<?php
import('base.controller.BaseTag');

class EmailTagTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->T->tag = array_get($params, 'tag', 'span');
        $this->T->content = array_get($params, 'content', '');
        return $this->T->return('tag.email-tag');
    }
}
?>