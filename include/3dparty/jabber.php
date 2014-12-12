<?php
// include the Jabber class
define('RUN_TIME',30); // максимальный срок работы скрипта. Здесь стоит 30 секунд.

import('3dparty.jabberclass.class_Jabber');

class JabberMessageSender
{
    protected $subject, $message;
    protected $username, $passw, $resource, $server;

    function __construct($username, $passw, $server, $resource = NULL)
    {
        $this->username = $username;
        $this->passw = $passw;
        $this->resource = $resource;
        $this->server = $server;
        
        //создать экземпляр основного класса
        $this->jab = new Jabber(true);
        $this->first_roster_update = true;
        
        $this->jab->set_handler('connected', $this, 'handleConnected');
        //$this->jab->set_handler('authenticated', $this, 'handleAuthenticated');
        $this->jab->connect($this->server);
    }

    //эта функция вызывается автоматически при установлении соединения
    function handleConnected() {
        //если соединение установлено, пытаемся авторизоваться…
        $this->jab->login($this->username, $this->passw);
    }

    //вызывается если авторизация прошла успешно
    function handleAuthenticated() {
        //собственно отправка сообщения
    }
    
    function sendMessage($sendto, $subject, $message){
        if ($this->jab->message($sendto, 'normal', NULL, $message, NULL, $subject)) {
            $this->jab->terminated = true;
        }
        
        // запуск цикла обработки событий
        //$this->jab->execute(1, RUN_TIME);
    }
    
    function disconnect()
    {
        //прощаемся с сервером…
        $this->jab->disconnect();
    }
}   
?>