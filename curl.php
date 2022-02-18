<?php
/**
 * 代码来自：https://www.laruence.com/2015/05/28/3038.html
 */
require "core/coroutines/Scheduler.php";

function test()
{
    $res = (yield retval(file_get_contents('https://www.baidu.com/')));
    var_dump($res);
}

$scheduler = new Scheduler();
$scheduler->newTask(test());
$scheduler->run();