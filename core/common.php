<?php

use core\coroutines\CoroutineReturnValue;
use core\coroutines\Scheduler;
use core\coroutines\SystemCall;
use core\coroutines\Task;

/**
 * 获取当前执行任务id
 * @return SystemCall
 */
function getTaskId(): SystemCall
{
    return new SystemCall(function (Task $task, Scheduler $scheduler) {
        $task->setSendValue($task->getTaskId());
        $scheduler->schedule($task);
    });
}

/**
 * 创建新任务
 * @param Generator $coroutine
 * @return SystemCall
 */
function newTask(Generator $coroutine): SystemCall
{
    return new SystemCall(
        function (Task $task, Scheduler $scheduler) use ($coroutine) {
            $task->setSendValue($scheduler->newTask($coroutine));
            $scheduler->schedule($task);
        }
    );
}

/**
 * 删除任务
 * @param $tid
 * @return SystemCall
 */
function killTask($tid): SystemCall
{
    return new SystemCall(
        function (Task $task, Scheduler $scheduler) use ($tid) {
            $task->setSendValue($scheduler->killTask($tid));
            $scheduler->schedule($task);
        }
    );
}

/**
 * socket 读取
 * @param $socket
 * @return SystemCall
 */
function waitForRead($socket): SystemCall
{
    return new SystemCall(
        function (Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForRead($socket, $task);
        }
    );
}

/**
 * socket 写入
 * @param $socket
 * @return \core\coroutines\SystemCall
 */
function waitForWrite($socket): SystemCall
{
    return new SystemCall(
        function (Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForWrite($socket, $task);
        }
    );
}

/**
 * 协程堆栈
 * @param Generator $gen
 * @return Generator|void
 */
function stackedCoroutine(Generator $gen)
{
    $stack = new SplStack;
    for (; ;) {
        $value = $gen->current();
        if ($value instanceof Generator) {
            $stack->push($gen);
            $gen = $value;
            continue;
        }
        $isReturnValue = $value instanceof CoroutineReturnValue;
        if (!$gen->valid() || $isReturnValue) {
            if ($stack->isEmpty()) {
                return;
            }
            $gen = $stack->pop();
            $gen->send($isReturnValue ? $value->getValue() : NULL);
            continue;
        }
        $gen->send(yield $gen->key() => $value);
    }
}

/**
 * 创建协程返回函数
 * @param $value
 * @return CoroutineReturnValue
 */
function retval($value): CoroutineReturnValue
{
    return new CoroutineReturnValue($value);
}