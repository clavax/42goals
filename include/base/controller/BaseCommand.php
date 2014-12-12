<?php
import('base.controller.BaseController');

abstract class BaseCommand extends BaseController
{
	abstract public function handle(array $request = array());
	
	public static function writeln($str)
	{
	    echo implode(', ', func_get_args()) . "\n";
	}
}
?>