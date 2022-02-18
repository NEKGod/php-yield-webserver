<?php
/**
 * 代码来自：https://www.laruence.com/2015/05/28/3038.html
 */
require "core/coroutines/Task.php";
require "core/coroutines/Scheduler.php";

function server($port) {
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
function handleClient($socket) {
    $data = (yield $socket->read(8192));
    $msg = "Received following request:\n\n$data";
    $msgLength = strlen($msg);
    $response = <<<res
HTTP/1.1 200 OK
Content-Type: text/html

$msg
res;
    yield $socket->write($response);
    yield $socket->close();
}
$scheduler = new Scheduler;
$scheduler->newTask(server(8000));
/** @noinspection PhpUnreachableStatementInspection */
$scheduler->run();
