<?php
require_once "vendor/autoload.php";

use core\coroutines\CoSocket;
use core\coroutines\Scheduler;


function server($port): Generator
{
    echo "Starting server at port $port...\n";
    $socket = @stream_socket_server("tcp://0.0.0.0:$port", $errNo, $errStr);
    if (!$socket) throw new Exception($errStr, $errNo);
    stream_set_blocking($socket, 0);
    $socket = new CoSocket($socket);
    while (true) {
        yield newTask(
            handleClient(yield $socket->accept())
        );
    }
}

/**
 * @param CoSocket $socket
 * @return \Generator
 */
function handleClient(CoSocket $socket): Generator
{
    $data = (yield $socket->read(8192));
    $msg = "Received following request:\n\n";
    $msgLength = strlen($msg);
    $response = <<<res
HTTP/1.1 200 OK
Content-Type: text/html

$msg
res;
    var_dump($response) ;
    yield $socket->write($response);
    yield $socket->close();
}
$scheduler = new Scheduler;
try {
    $scheduler->newTask(server(8000));
    /** @noinspection PhpUnreachableStatementInspection */
    $scheduler->run();
} catch (Exception $e) {

}