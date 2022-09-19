<?php
namespace core\coroutines;

class CoSocket
{
    protected $socket;

    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    public function accept()
    {
        yield waitForRead($this->socket);
        yield retval(new CoSocket(stream_socket_accept($this->socket, 0)));
    }

    public function read($size)
    {
        yield waitForRead($this->socket);
        yield retval(fread($this->socket, $size));
    }

    public function write($string)
    {
        yield waitForWrite($this->socket);
        fwrite($this->socket, $string);
    }

    public function close()
    {
        @fclose($this->socket);
    }
}