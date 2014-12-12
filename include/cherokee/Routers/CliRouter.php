<?php
import('cherokee.Routers.BaseRouter');

class CliRouter extends BaseRouter
{
	protected function getQuery()
	{
		return isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : false;
	}
	
	protected function getRequest()
	{
		
	}
	
	protected function callController($controller)
	{
        $content = false;
        if (method_exists($controller, 'handle')) {
            $this->PTH->this = dirname($this->ENV->controller) . '/';
            $content = $controller->handle($_SERVER['argv']);
        }

        return $content;		
	}
}
?>