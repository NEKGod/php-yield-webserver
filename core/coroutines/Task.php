<?php
namespace core\coroutines;

use Generator;
use SplStack;

/**
 * 任务类
 */
class Task
{
    protected $taskId; // 任务id
    protected $coroutine; // 协程对象
    protected $sendValue = null; //
    protected $beforeFirstYield = true; // 是否为第一次调用

    public function __construct($taskId, Generator $coroutine)
    {
        $this->taskId    = $taskId;
        $this->coroutine = StackedCoroutine($coroutine);
    }

    public function getTaskId()
    {
        return $this->taskId;
    }

    public function setSendValue($sendValue)
    {
        $this->sendValue = $sendValue;
    }

    public function run()
    {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        }

        $retrieval = $this->coroutine->send($this->sendValue);
        $this->sendValue = null;
        return $retrieval;
    }

    /**
     * 任务是否已结束
     * @return bool
     */
    public function isFinished(): bool
    {
        return !$this->coroutine->valid();
    }
}