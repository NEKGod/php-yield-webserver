<?php
require_once "core/coroutines/Scheduler.php";

function test()
{
    yield 'test';
    $res = (yield test1());
    echo $res;
}
function test1()
{
    yield 'test1';
    yield retval('return'.PHP_EOL);
    yield (test2());

}
function test2()
{
    yield 'test2';
}
function main()
{
    yield test();
    yield 'exit';
}
$res = stackedCoroutine(main());
foreach($res as $vo){
    echo $vo.PHP_EOL;
}

//$gen = test();
//$res = $gen->current();
//var_dump($res);
//$Scheduler = new Scheduler();
//$Scheduler->newTask(test1());
//$Scheduler->run();