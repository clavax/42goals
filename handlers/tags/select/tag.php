<?php
import('base.controller.BaseTag');

class SelectTag extends BaseTag
{
    public function handle(array $params = array())
    {
        if (isset($params['options']) && arrays::nonempty($params['options'])) {
            $options = $params['options'];
            $value = isset($params['value']) ? $params['value'] : null;
            $this->T->assign('options', $options);
            $this->T->assign('value', $value);
            unset($params['options'], $params['value'], $params['content']);
            $this->T->assign('params', $params);
            return $this->T->return_template('tag.select');
        } else {
            return 'Error: no options defined for select element';
        }
    }
}
?>