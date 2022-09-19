<?php
namespace core\coroutines;

use Generator;
use SplQueue;

/**
 * 系统任务调度器
 * 主体核心
 * 用于调用 协程 让所有的协程 根据队列交替执行
 */
class Scheduler
{
    protected $maxTaskId = 0; // 分配任务id
    protected $taskMap = [];  // taskId => task
    protected $taskQueue;     // 任务队列

    public function __construct()
    {
        $this->taskQueue = new SplQueue();
    }

    /**
     * 运行调度器 执行任务
     * @return void
     */
    public function run()
    {
        $this->newTask($this->ioPollTask());
        while (!$this->taskQueue->isEmpty()) {
            /** @var Task $task */
            $task = $this->taskQueue->dequeue();
            $retrieval = $task->run();
            /**
             * 任务中有系统调用函数执行
             */
            if ($retrieval instanceof SystemCall) {
                $retrieval($task, $this);
                continue;
            }
            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->schedule($task);
            }
        }
    }

    /**
     * 任务 追加
     * @param Generator $coroutine
     * @return int
     */
    public function newTask(Generator $coroutine)
    {
        $tid = ++$this->maxTaskId;
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    /**
     * 协程最小调度单位 yield 加入调用任务队列
     * @param Task $task
     * @return void
     */
    public function schedule(Task $task)
    {
        $this->taskQueue->enqueue($task);
    }

    /**
     * 删除任务
     * @param $tid
     * @return bool
     */
    public function killTask($tid)
    {
        if (!isset($this->taskMap[$tid])) {
            return false;
        }
        // 移除task任务映射
        unset($this->taskMap[$tid]);
        // 移除任务队列
        foreach ($this->taskQueue as $i => $task) {
            if ($task->getTaskId() === $tid) {
                unset($this->taskQueue[$i]);
                break;
            }
        }
        return true;
    }

    protected $waitingForRead = [];
    protected $waitingForWrite = [];

    // resourceID => [socket, tasks]

    public function waitForRead($socket, Task $task)
    {
        if (isset($this->waitingForRead[(int)$socket])) {
            $this->waitingForRead[(int)$socket][1][] = $task;
        } else {
            $this->waitingForRead[(int)$socket] = [$socket, [$task]];
        }
    }

    public function waitForWrite($socket, Task $task)
    {
        if (isset($this->waitingForWrite[(int)$socket])) {
            $this->waitingForWrite[(int)$socket][1][] = $task;
        } else {
            $this->waitingForWrite[(int)$socket] = [$socket, [$task]];
        }
    }

    protected function ioPollTask()
    {
        while (true) {
            if ($this->taskQueue->isEmpty()) {
                $this->ioPoll(null);
            } else {
                $this->ioPoll(0);
            }
            yield;
        }
    }

    protected function ioPoll($timeout)
    {
        $rSocks = [];
        foreach ($this->waitingForRead as list($socket)) {
            $rSocks[] = $socket;
        }
        $wSocks = [];
        foreach ($this->waitingForWrite as list($socket)) {
            $wSocks[] = $socket;
        }
//        var_dump($rSocks);
//        var_dump($wSocks);
        $eSocks = []; // dummy
        if (!stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
            return;
        }
        foreach ($rSocks as $socket) {
            list(, $tasks) = $this->waitingForRead[(int)$socket];
            unset($this->waitingForRead[(int)$socket]);
            foreach ($tasks as $task) {
                $this->schedule($task);
            }
        }
        foreach ($wSocks as $socket) {
            list(, $tasks) = $this->waitingForWrite[(int)$socket];
            unset($this->waitingForWrite[(int)$socket]);
            foreach ($tasks as $task) {
                $this->schedule($task);
            }
        }
    }
}





