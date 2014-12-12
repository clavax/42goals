<?php
class Object
{
    public function __get($name)
    {
        $F = Framework::instance();
        if ($name == 'F') {
            return $F;
        }
        
        $value = Framework::get($name);
        if ($value === false) {
            $Conf = Framework::get('Conf');
            $value = $Conf->get($name, false);
        }

        return $value;
    }
}
?>