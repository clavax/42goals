<?php
import('base.controller.BaseCommand');

abstract class BaseDaemon extends BaseCommand
{
    const CMD_TEST     = 'test';
    const CMD_START    = 'start';
    const CMD_STOP     = 'stop';
    const CMD_RUN      = 'run';
    const CMD_RESTART  = 'restart';
    const CMD_HELP     = 'help';
    
    const MSG_QUIT     = 'quit';
    const MSG_SHUTDOWN = 'shutdown';
    
    const STAT_NONE     = 0;
    const STAT_QUIT     = 1;
    const STAT_SHUTDOWN = 2;
    
    protected $cmd = '';
    protected $port = 0;
    
    protected $address = '127.0.0.1';
    protected $sock = null;
    
    public function handle(array $request = array())
    {
        $command = trim(array_get($request, 2));
        $content = '';
        
        switch ($command) {
        case self::CMD_TEST:
            $content = $this->test();
            break;
            
        case self::CMD_RUN:
            $content = $this->run();
            break;
            
        case self::CMD_START:
            $content = $this->start();
            break;
            
        case self::CMD_STOP:
            $content = $this->stop();
            break;
            
        case self::CMD_RESTART:
            $this->stop();
            sleep(5);
            $content = $this->start();
            break;
            
        default:
            if (method_exists($this, 'handle' . $command)) {
                $content = call_user_func(array(&$this, 'handle' . $command), $request);
            }
        }
        
        return $content;
    }
    
    protected function run()
    {
        /* Allow the script to hang around waiting for connections. */
        set_time_limit(0);
        
        /* Turn on implicit output flushing so we see what we're getting
         * as it comes in. */
        ob_implicit_flush();
        
        if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            $this->Error->log("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
            return false;
        }
        
        if (socket_bind($sock, $this->address, $this->port) === false) {
            $this->Error->log("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)));
            return false;
        }
        
        if (socket_listen($sock, 5) === false) {
            $this->Error->log("socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)));
            return false;
        }
        
        $this->onInit();
        
        $status = self::STAT_NONE;
        
        socket_set_nonblock($sock);
        
        do {
            if (($msgsock = @socket_accept($sock)) === false) {
                $this->onEmpty();
                sleep(1);
                continue;
            }
            
            $this->sock = $msgsock;

            do {
                if (false === ($buf = $this->read())) {
                    $this->Error->log("socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)));
                    break 2;
                }
                $buf = trim($buf);
                if (!strlen($buf)) {
                    continue;
                }
                
                $status = $this->act($buf);

            } while ($status < self::STAT_QUIT);
            
            socket_close($msgsock);
            $this->sock = null;
            
        } while (is_resource($sock) && $status < self::STAT_SHUTDOWN);
        
        $this->onShutdown();
        
        socket_close($sock);
    }
    
    protected function onEmpty()
    {
        
    }
    
    
    protected function onInit()
    {
        
    }
    
    protected function onShutdown()
    {
        
    }
    
    protected function read()
    {
        return socket_read($this->sock, 2048, PHP_NORMAL_READ);
    }
    
    protected function write($message)
    {
        socket_write($this->sock, $message, strlen($message));
    }
    
    protected function act($message)
    {
        $status = self::STAT_NONE;
        
        switch ($message) {
        case self::MSG_QUIT:
            $status = self::STAT_QUIT;
            break;
            
        case self::MSG_SHUTDOWN:
            $status = self::STAT_SHUTDOWN;
            break;
            
        default:
        }
        
        return $status;
    }
    
    protected function start()
    {
        exec("{$this->PTH->main}cli.php {$this->cmd} run > /dev/null 2>&1 &"); // won't work on Windows
        return "Started Daemon on {$this->address}:{$this->port}\n";
    }
    
    protected function stop()
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            return "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        }
        
        $result = socket_connect($socket, $this->address, $this->port);
        if ($result === false) {
            return "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        }
        
        $in = 'shutdown';
        socket_write($socket, $in, strlen($in));
        
        socket_close($socket);
        
        return "Stopped Daemon \n";
    }
    
    protected function test()
    {
        return 'Not implemented';
    }
}
?>