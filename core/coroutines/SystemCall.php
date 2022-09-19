<?php
namespace core\coroutines;

use Generator;

/**
 * 系统回调
 * 在调度器（Scheduler）run方法中增加钩子
 * 为了程序在运行中还可以添加新的任务（task）
 */
class SystemCall
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(Task $task, Scheduler $scheduler)
    {
        $callback = $this->callback;
        return $callback($task, $scheduler);
    }
}
