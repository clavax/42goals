#! /usr/bin/php
<?php
set_time_limit(120);

$_SERVER['PHP_SELF'] = '/';
$_SERVER['HTTP_HOST'] = '42goals.com';

require_once 'include/cherokee/Framework.php';
$Cherokee = Framework::instance();
$Cherokee->load();

$Conf   = $Cherokee->Conf;
$Timer  = $Cherokee->Timer;

$Conf->URL->home = '/';

// register database
Framework::register('db', 'Database::plug_n_play', array($Conf->CNF->database->type, Database::MODE_DEV, $Conf->CNF->database));

import('cherokee.Routers.CliRouter');
$Router = new CliRouter();
$Router->addRoute(new CliRoute);
$content = $Router->route();
$Timer->stop('Load controller');
$Cherokee->Mem->stop();

echo Framework::addEnviroment($content);
?>
