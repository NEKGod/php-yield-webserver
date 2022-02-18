<?php

/**
 * 协程返回值
 */
class CoroutineReturnValue
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

/**
 * 创建协程返回函数
 * @param $value
 * @return CoroutineReturnValue
 */
function retval($value)
{
    return new CoroutineReturnValue($value);
}