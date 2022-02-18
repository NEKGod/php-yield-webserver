<?php

class Sockets
{
    private $socket;

    private $readContent;

    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    public function getContent()
    {
        if ($this->readContent) {
            return $this->readContent;
        }
        $this->readContent = socket_read($this->socket, 1024 * 1024);
        return $this->readContent;
    }

    public function sendContent($content)
    {
        return socket_write($this->socket, $content,strlen($content));
    }

    public function close()
    {
        socket_close($this->socket);
    }

    public function getSocket()
    {
        return $this->socket;
    }
}