<?php
import('cherokee.Routes.StaticRoute');

class DomainedStaticRoute extends StaticRoute
{
    public function getController($query)
    {
        $domain = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
        $this->controllers_dir = $this->PTH->domains . $domain . '/';
        if (!is_dir($this->controllers_dir)) {
            $this->controllers_dir = $this->PTH->static;
        }
        return parent::getController($query);
    }
}