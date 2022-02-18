<?php
require_once "Sockets.php";

class SocketListen
{
    private $socket;

    public function __construct($ip, $port)
    {
        if(($sock = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) < 0) {
            die("socket_create() 失败的原因是:".socket_strerror($sock)."\n");
        }
        if(($ret = socket_bind($sock ,$ip, $port)) < 0) {
            die("socket_bind() 失败的原因是:".socket_strerror($ret)."\n");
        }
        if(($ret = socket_listen($sock,2000)) < 0) {
            die("socket_listen() 失败的原因是:".socket_strerror($ret)."\n");
        }
        $this->socket = $sock;
    }

    /**
     * @return Sockets
     */
    public function popSocket()
    {
        return new Sockets(socket_accept($this->getSocket()));
    }

    public function getSocket(){
        return $this->socket;
    }

}