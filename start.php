<?php
require_once "./core/socket/SocketListen.php";

//确保在连接客户端时不会超时
set_time_limit(0);
ini_set("memory_limit", '1024M');

$ip = '0.0.0.0';
$port = 1935;
$socketListen = new SocketListen($ip, $port);


do{
    $socket = $socketListen->popSocket();
    var_dump($socket);
    $content = $socket->getContent();
    var_dump($content);
    $s = $socket->sendContent("HTTP/1.1 200 OK
Content-Type: text/html

hello world\r");
    var_dump($s);
    $socket->close();
}while(true);

socket_close($socketListen->getSocket());