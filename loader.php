<?php
set_time_limit(60);

require_once 'include/cherokee/Framework.php';
$Cherokee = Framework::instance();
$Cherokee->load();

$Conf   = $Cherokee->Conf;
$Timer  = $Cherokee->Timer;

// register database
Framework::register('db', 'Database::plug_n_play', array($Conf->CNF->database->type, Database::MODE_DEV, $Conf->CNF->database));

// start session
$Session = Session::instance();
Framework::set('Session', $Session);
$Timer->reg('Session is started');

Framework::set('Twitter', new Twitter($Conf->CNF->twitter->key, $Conf->CNF->twitter->secret));

import('cherokee.Routers.HttpRouter');
$Router = new HttpRouter();
$Router->setErrorRoute(new ErrorRoute);
$Router->addRoute(new UserPageRoute);
$Router->addRoute(new DomainedStaticRoute);
$content = $Router->route();
$Timer->stop('Load controller');
$Cherokee->Mem->stop();

$content = Framework::addEnviroment($content);

if ($Conf->CNF->site->gzip_enabled && preg_match('/\Wgzip\W/i', array_get($_SERVER, 'HTTP_ACCEPT_ENCODING'))) {
    header('Content-Encoding: gzip');
    
    $gzip_size = strlen($content);
    $gzip_crc = crc32($content);

    $content = gzcompress($content, 9);
    $content = substr($content, 0, strlen($content) - 4);

    $content = "\x1f\x8b\x08\x00\x00\x00\x00\x00"
             . $content
             . pack('V', $gzip_crc)
             . pack('V', $gzip_size);
}

echo $content;
?>