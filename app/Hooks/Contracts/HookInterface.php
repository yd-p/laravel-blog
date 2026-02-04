<?php

namespace App\Hooks\Contracts;

/**
 * 钩子接口
 * 所有钩子类都应该实现此接口
 */
interface HookInterface
{
    /**
     * 处理钩子逻辑
     * 
     * @param mixed ...$args 钩子参数
     * @return mixed 处理结果
     */
    public function handle(...$args);

    /**
     * 获取钩子描述
     * 
     * @return string
     */
    public function getDescription(): string;

    /**
     * 获取钩子优先级
     * 
     * @return int
     */
    public function getPriority(): int;
}