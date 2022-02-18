<?php

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
    public function isFinished()
    {
        return !$this->coroutine->valid();
    }
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